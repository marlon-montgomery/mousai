<?php namespace Common\Auth\Controllers;

use App\User;
use Auth;
use Common\Core\BaseController;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\VerifiesEmails;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VerifyEmailController extends BaseController
{
    use VerifiesEmails;

    /**
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * @var Request
     */
    private $request;

    /**
     * @var User
     */
    private $user;

    /**
     * @param Request $request
     * @param User $user
     */
    public function __construct(Request $request, User $user)
    {
        $this->user = $user;
        $this->request = $request;
    }

    public function verify(Request $request)
    {
        $user = User::find($request->route('id'));

        if ( ! hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification()))) {
            throw new AuthorizationException;
        }

        if ($user->hasVerifiedEmail() || $user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        if ( ! Auth::check()) {
            Auth::login($user);
        }

        return redirect('/');
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function resend(Request $request)
    {
        /** @var MustVerifyEmail $user */
        $user = User::where('email', $request->get('email'))->firstOrFail();

        if ($user->hasVerifiedEmail()) {
            return $this->error(__('Already verified.'));
        }

        $user->sendEmailVerificationNotification();

        return $this->success();
    }
}
