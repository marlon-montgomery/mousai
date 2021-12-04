<?php namespace Common\Auth\Controllers;

use App\User;
use Auth;
use Common\Auth\Oauth;
use Common\Auth\Requests\SocialAuthBitclout;
use Common\Auth\Roles\Role;
use Common\Auth\SocialProfile;
use Common\Core\BaseController;
use Common\Core\Bootstrap\BootstrapData;
use Common\Settings\Settings;
use Elliptic\EC;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Log;
use Muvon\KISS\Base58Codec;
use Session;

class SocialAuthController extends BaseController
{
    /**
     * @var Oauth
     */
    private $oauth;

    /**
     * Laravel request instance.
     */
    private $request;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var BootstrapData
     */
    private $bootstrapData;

    public function __construct(
        Request $request,
        Oauth $oauth,
        Settings $settings,
        BootstrapData $bootstrapData
    ) {
        $this->oauth = $oauth;
        $this->request = $request;
        $this->settings = $settings;
        $this->bootstrapData = $bootstrapData;

        $this->middleware('auth', ['only' => ['connect', 'disconnect']]);
        $this->middleware('guest', ['only' => ['login']]);

        //abort if registration should be disabled
        if ($this->settings->get('disable.registration')) {
            abort(404);
        }
    }

    /**
     * Connect specified social account to currently logged in user.
     *
     * @param string $provider
     * @return mixed
     */
    public function connect($provider)
    {
        return $this->oauth->connectCurrentUserTo($provider);
    }

    public function retrieveProfile(string $providerName)
    {
        return $this->oauth->retrieveProfileOnly($providerName);
    }

    /**
     * Disconnect specified social account from currently logged in user.
     *
     * @param string $provider
     * @return mixed
     */
    public function disconnect($provider)
    {
        return $this->oauth->disconnect($provider);
    }

    /**
     * Login with specified social provider.
     *
     * @param string $provider
     * @return mixed
     */
    public function login($provider)
    {
        return $this->oauth->loginWith($provider);
    }

    public function loginCallback(string $provider)
    {
        if ($handler = Session::get(Oauth::OAUTH_CALLBACK_HANDLER_KEY)) {
            return app($handler)->execute($provider);
        }

        $externalProfile = null;
        try {
            $externalProfile = $this->oauth->socializeWith(
                $provider,
                $this->request->get('tokenFromApi'),
                $this->request->get('secretFromApi'),
            );
        } catch (Exception $e) {
            Log::error($e);
        }

        if (!$externalProfile) {
            return $this->oauth->getErrorResponse(
                __('Could not retrieve social sign in account.'),
            );
        }

        // TODO: use new "OAUTH_CALLBACK_HANDLER_KEY" functionality to handle this, remove "tokenFromApi" stuff from this handler
        if (Session::get(Oauth::RETRIEVE_PROFILE_ONLY_KEY)) {
            Session::forget(Oauth::RETRIEVE_PROFILE_ONLY_KEY);
            return $this->oauth->returnProfileData($externalProfile);
        }

        $existingProfile = $this->oauth->getExistingProfile($externalProfile);

        // if user is already logged in, attach returned social account to logged in user
        if (Auth::check()) {
            return $this->oauth->attachProfileToExistingUser(
                Auth::user(),
                $externalProfile,
                $provider,
            );
        }

        // if we have already created a user for this social account, log user in
        if ($existingProfile && $existingProfile->user) {
            return $this->oauth->logUserIn($existingProfile->user);
        }

        //if user is trying to log in with envato and does not have any valid purchases, bail
        if ($provider === 'envato' && empty($externalProfile->purchases)) {
            return $this->oauth->getErrorResponse(
                'You do not have any supported purchases.',
            );
        }

        $credentials = $this->oauth->getCredentialsThatNeedToBeRequested(
            $externalProfile,
            $provider,
        );

        //we need to request some extra credentials from user before creating account
        if (!empty($credentials)) {
            return $this->oauth->requestExtraCredentials(
                $credentials,
                $provider,
                $externalProfile,
            );

            //if we have email and didn't create an account for this profile yet, do it now
        } else {
            return $this->oauth->createUserFromOAuthData([
                'profile' => $externalProfile,
                'service' => $provider,
            ]);
        }
    }

    /**
     * Process extra credentials supplied by user
     * that were needed to complete social login.
     * (Password, email, purchase code etc)
     *
     * @return JsonResponse
     */
    public function extraCredentials()
    {
        // get data for this social login persisted in session
        $data = $this->oauth->getPersistedData();

        if (!$data) {
            return $this->error(__('Could not log you in. Please try again.'));
        }

        // validate user supplied extra credentials
        $errors = $this->oauth->validateExtraCredentials($this->request->all());

        if (!empty($errors)) {
            return $this->error(
                __('Specified credentials are not valid'),
                $errors,
            );
        }

        if (!isset($data['profile']->email)) {
            $data['profile']->email = $this->request->get('email');
        }

        return $this->success([
            'data' => $this->oauth->createUserFromOAuthData($data),
        ]);
    }

    public function bitclout(SocialAuthBitclout $request)
    {
        ['publicKey' => $publicKey, 'jwt' => $jwt] = $request->validated();

        $pkHex = Base58Codec::checkDecode($publicKey);
        if (!$pkHex) {
            return $this->error('Invalid public key');
        }

        try {
            $ec = new EC('secp256k1');
            $pkFull = $ec->keyFromPublic(substr($pkHex, 6), 'hex')->getPublic(false, 'hex');
            $pkBase64 = base64_encode(hex2bin('3056301006072a8648ce3d020106052b8104000a034200' . $pkFull));
            $keyMaterial = sprintf('-----BEGIN PUBLIC KEY-----%s-----END PUBLIC KEY-----', PHP_EOL . $pkBase64 . PHP_EOL);
            JWT::decode($jwt, new Key($keyMaterial, 'ES256'));
        } catch (Exception $e) {
            return $this->error('Invalid verify public key');
        }

        try {
            $profile = Http::withHeaders(['Content-Type' => 'application/json'])
                ->post('https://api.bitclout.com/api/v0/get-single-profile', [
                    'PublicKeyBase58Check' => $publicKey,
                ])
                ->throw()
                ->json('Profile');
        } catch (Exception $e) {
            return $this->error('Error fetch profile');
        }

        if (empty($profile['Username'])) {
            return $this->error('Profile not verify');
        }

        $socialProfile = SocialProfile::where('user_service_id', $publicKey)
            ->where('service_name', 'bitclout')
            ->with('user')
            ->first();

        if (!$socialProfile) {
            $user = User::create([
                'username' => $profile['Username'],
                'email' => $profile['Username'] . '@bitclout.com',
                'password' => Hash::make(Str::random())
            ]);

            $user->roles()->attach(Role::where('default', 1)->first() ?? 1);

            SocialProfile::create([
                'user_id' => $user->id,
                'service_name' => 'bitclout',
                'user_service_id' => $publicKey,
                'username' => $profile['Username'],
            ]);

            $socialProfile = SocialProfile::where('user_service_id', $publicKey)
                ->where('service_name', 'bitclout')
                ->with('user')
                ->first();
        } else {
            $user = $socialProfile->user;
        }

        Auth::login($user, true);

        $data = $this->bootstrapData->init()->getEncoded();

        return $this->success(['data' => $data]);
    }
}
