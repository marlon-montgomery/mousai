<?php namespace Common\Auth;

use App\User;
use Common\Core\Bootstrap\MobileBootstrapData;
use Exception;
use Common\Settings\Settings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use View, Auth, Session;
use Laravel\Socialite\Contracts\Factory as SocialiteFactory;

class Oauth {

    const RETRIEVE_PROFILE_ONLY_KEY = 'retrieveProfileOnly';

    private $validProviders = ['google', 'facebook', 'twitter', 'envato'];

    /**
     * @var User
     */
    private $user;

    /**
     * @var SocialiteFactory
     */
    private $socialite;

    /**
     * @var UserRepository
     */
    private $userCreator;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @param SocialiteFactory $socialite
     * @param UserRepository $userCreator
     * @param Settings $settings
     * @param User $user
     */
    public function __construct(
        SocialiteFactory $socialite,
        UserRepository $userCreator,
        Settings $settings,
        User $user
    )
    {
        $this->settings = $settings;
        $this->socialite   = $socialite;
        $this->userCreator = $userCreator;
        $this->user = $user;
    }

    /**
     * Log user in with provider service or throw 404 if service is not valid.
     *
     * @param string $provider
     * @return mixed
     */
    public function loginWith($provider)
    {
        if (Auth::user()) {
           return View::make('common::oauth/popup')->with('status', 'ALREADY_LOGGED_IN');
        }

        return $this->connectCurrentUserTo($provider);
    }

    /**
     * Connect currently logged in user to specified social account.
     * @return mixed
     */
    public function connectCurrentUserTo(string $providerName)
    {
        $this->validateProvider($providerName);

        return $this->socialite->driver($providerName)->redirect();
    }

    /**
     * Retrieve user details from specified social account without logging user in or connecting accounts.
     * @return mixed
     */
    public function retrieveProfileOnly(string $providerName)
    {
        $this->validateProvider($providerName);

        Session::put([Oauth::RETRIEVE_PROFILE_ONLY_KEY => true]);

        $driver = $this->socialite->driver($providerName);

        // get user profile url from facebook
        if ($providerName === 'facebook') {
            $driver->scopes(['user_link']);
        }

        return $driver->redirect();
    }

    /**
     * Disconnect specified social social account from currently logged in user.
     *
     * @param string $provider
     * @return mixed
     */
    public function disconnect($provider)
    {
        $this->validateProvider($provider);

        return Auth::user()->social_profiles()->where('service_name', $provider)->delete();
    }

    /**
     * Get user profile from specified social provider or throw 404 if it's invalid.
     *
     * @param string $provider
     * @param string|null $token
     * @param string|null $secret
     * @return mixed
     */
    public function socializeWith($provider, ?string $token, ?string $secret)
    {
        $this->validateProvider($provider);

        if ($token && $secret) {
            $user = $this->socialite->driver($provider)->userFromTokenAndSecret($token, $secret);
        } else if ($token) {
            $user = $this->socialite->driver($provider)->userFromToken($token);
        } else {
            $user = $this->socialite->with($provider)->user();
        }

        //persist envato purchases in session if user is signing in
        //with envato, so we can attach them to user later
        if ($provider === 'envato' && ! empty($user->purchases)) {
            $this->persistSocialProfileData(['envato_purchases' => $user->purchases]);
        }

        return $user;
    }

    /**
     * Return existing social profile from database for specified external social profile.
     *
     * @param $profile
     * @return SocialProfile|null
     */
    public function getExistingProfile($profile)
    {
        if ( ! $profile) return null;

        return SocialProfile::where('user_service_id', $this->getUsersIdentifierOnService($profile))->with('user')->first();
    }

    /**
     * Return user matching given email from database.
     *
     * @param  string $email
     * @return User|null
     */
    public function findUserByEmail($email = null)
    {
        if ( ! $email) return;

        return $this->user->where('email', $email)->first();
    }

    /**
     * Create a new user from given social profile and log him in.
     *
     * @param  array $data
     * @return mixed
     */
    public function createUserFromOAuthData(array $data)
    {
        $profile = $data['profile'];
        $service = $data['service'];

        $user = $this->findUserByEmail($profile->email);

        //create a new user if one does not exist with specified email
        if ( ! $user) {
            $img = str_replace('http://', 'https://', $profile->avatar);
            $user = $this->userCreator->create(['email' => $profile->email, 'avatar' => $img]);
        }

        //save this social profile data, so we can login the user easily next time
        $user->social_profiles()->create($this->transformSocialProfileData($service, $profile, $user));

        //save data about user supplied envato purchase code
        if ($purchases = $this->getPersistedData('envato_purchases')) {
            $user->updatePurchases($purchases, $profile->nickname);
        }

        return $this->logUserIn($user);
    }

    /**
     * Attach specified social profile to user.
     *
     * @param User $user
     * @param Object $profile
     * @param string $service
     *
     * @return \Illuminate\Contracts\View\View|array
     */
    public function attachProfileToExistingUser($user, $profile, $service)
    {
        $payload = $this->transformSocialProfileData($service, $profile, $user);

        //if this social account is already attached to some user
        //we will re-attach it to specified user
        if ($existing = $this->getExistingProfile($profile)) {
            $existing->forceFill($payload)->save();

        //if social account is not attached to any user, we will
        //create a model for it and attach it to specified user
        } else {
            $user->social_profiles()->create($payload);
        }

        //save data about user supplied envato purchase code
        if ($purchases = $this->getPersistedData('envato_purchases')) {
            $user->updatePurchases($purchases, $profile->nickname);
        }

        $user = $user->load('social_profiles')->loadPermissions()->toArray();

        return request()->expectsJson()
            ? ['status' => 'success', 'user' => $user]
            : $this->getPopupResponse('SUCCESS_CONNECTED', ['user' => $user]);
    }

    /**
     * Transform social profile into data acceptable by SocialProfile model.
     *
     * @param string $service
     * @param Object $profile
     * @param User $user
     *
     * @return array
     */
    private function transformSocialProfileData($service, $profile, $user)
    {
        return [
            'service_name'    => $service,
            'user_service_id' => $this->getUsersIdentifierOnService($profile),
            'user_id'         => $user->id,
            'username'        => $profile->name,
        ];
    }

    public function returnProfileData($externalProfile)
    {
        $normalizedProfile = [
            'id' => $externalProfile->id,
            'name' => $externalProfile->name,
            'email' => $externalProfile->email,
            'avatar' => $externalProfile->avatar,
            'profileUrl' => $externalProfile->profileUrl,
        ];

        if (request()->expectsJson()) {
            return ['profile' => $normalizedProfile];
        } else {
            return $this->getPopupResponse('SUCCESS_PROFILE_RETRIEVE', ['profile' => $normalizedProfile]);
        }
    }

    /**
     * Log given user into the app and return
     * a view to close popup in front end.
     *
     * @param  User $user
     *
     * @return string|array
     */
    public function logUserIn(User $user)
    {
        $user = Auth::loginUsingId($user->id, true)->loadPermissions();
        $response =  ['user' => $user->toArray()];
        if (request()->get('tokenForDevice')) {
            $response = app(MobileBootstrapData::class)
                ->init()
                ->refreshToken(request()->get('tokenForDevice'))
                ->get();
        }
        if (request()->expectsJson()) {
            return $response;
        } else {
            return $this->getPopupResponse('SUCCESS', $response);
        }
    }

    /**
     * Get a list of extra credentials that we need to request user
     * in order finalize his sign in with social service.
     *
     * @param object $profile
     * @param string $service
     * @return array
     */
    public function getCredentialsThatNeedToBeRequested($profile, $service)
    {
        $credentialsToRequest = [];

        //if this service didn't return user email, we'll need to request it
        if ( ! isset($profile->email)) {
            $credentialsToRequest[] = 'email';
        }

        //if we din't create a social profile for this service yet, but
        //user with returned email already exists in database, ask user to
        //enter password, and if it matches connect account to service
        $user = $this->findUserByEmail($profile->email);
        if (isset($profile->email) && $user && $user->password) {
            $credentialsToRequest[] = 'password';
        }

        return $credentialsToRequest;
    }

    /**
     * Request user specified extra credentials.
     *
     * @param array $credentials
     * @param string $service
     * @param Object $profile
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function requestExtraCredentials($credentials, $service, $profile)
    {
        $this->persistSocialProfileData([
            'service' => $service,
            'profile' => $profile,
            'requested_extra_credentials' => $credentials
        ]);

        return $this->getPopupResponse('REQUEST_EXTRA_CREDENTIALS', $credentials);
    }

    /**
     * Validate extra credentials supplied by user.
     *
     * @param array $input
     * @return array
     */
    public function validateExtraCredentials($input)
    {
        // get a list of credentials that we've requested from user
        $credentials = $this->getPersistedData('requested_extra_credentials');

        $errors = [];

        // validate password supplied by user against existing account or supplied email address
        if (in_array('password', $credentials) && ! $this->callbackPasswordIsValid($input)) {
            $errors['password'] = 'Incorrect password. Please try again.';
        }

        // if email user supplied already exists and user did not supply a password, show an error
        if ( ! isset($input['password']) && in_array('email', $credentials) && $this->findUserByEmail($input['email'])) {
            $errors['email'] = 'Email already exists. Please specify password.';
        }

        return $errors;
    }

    /**
     * Get error response with option error message.
     *
     * @param string $message
     * @return Response|JsonResponse
     */
    public function getErrorResponse($message = null)
    {
        if (request()->wantsJson()) {
            return response()->json(['message' => $message], 500);
        } else {
            return response()->view('common::oauth.popup', ['status' => 'ERROR', 'data' => $message], 500);
        }
    }

    /**
     * Get oauth data persisted in current session.
     *
     * @param string $key
     * @return mixed
     */
    public function getPersistedData($key = null)
    {
        //test session when not logged, what if multiple users log in at same time etc

        $data = Session::get('social_profile');

        if ( ! $key) return $data;

        if ($key && isset($data[$key])) {
            return $data[$key];
        }
    }

    /**
     * Validate that the password we requested from
     * user matches the one on account stored in session
     *
     * @param array $credentials
     * @return bool
     */
    public function callbackPasswordIsValid($credentials)
    {
        try {
            if ( ! isset($credentials['email'])) {
                $credentials['email'] = $this->getPersistedData('profile')->email;
            }

            return Auth::validate([
                'email'    => $credentials['email'],
                'password' => $credentials['password']
            ]);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Store specified social profile information in the session
     * for use in subsequent social login process steps.
     *
     * @param array $data
     */
    private function persistSocialProfileData($data)
    {
        foreach($data as $key => $value) {
            Session::put("social_profile.$key", $value);
        }
    }

    /**
     * Check if provider user want to login with is valid, if not throw 404
     * @param string $provider
     */
    private function validateProvider($provider)
    {
        if ( ! in_array($provider, $this->validProviders)) {
            abort(404);
        }
    }

    /**
     * Get users unique identifier on social service from given profile.
     *
     * @param Object $profile
     * @return string|integer
     */
    private function getUsersIdentifierOnService($profile)
    {
        return $profile->id ?? $profile->email;
    }

    /**
     * Return response to frontend social login popup.
     *
     * @param string $status
     * @param mixed $data
     *
     * @return \Illuminate\Contracts\View\View
     */
    private function getPopupResponse($status, $data = null)
    {
        $view = View::make('common::oauth/popup')->with('status', $status);

        if ($data) {
            $view->with('data', json_encode($data));
        }

        return $view;
    }
}
