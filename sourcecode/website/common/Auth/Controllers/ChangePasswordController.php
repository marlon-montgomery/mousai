<?php namespace Common\Auth\Controllers;

use Hash;
use App\User;
use Illuminate\Http\Request;
use Common\Core\BaseController;

class ChangePasswordController extends BaseController
{
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

    /**
     * @param int $userId
     * @return User
     */
    public function change($userId)
    {
        $user = $this->user->findOrFail($userId);

        $this->authorize('update', $user);

        $this->validate($this->request, $this->rules($user));

        $password = Hash::make($this->request->get('new_password'));
        $user->forceFill(['password' => $password])->save();

        return $user;
    }

    /**
     * @param User $user
     * @return array
     */
    private function rules(User $user)
    {
        $rules = [
            'new_password' => 'required|confirmed'
        ];

        if ($user->hasPassword) {
            $rules['current_password'] = "required|hash:{$user->password}";
            $rules['new_password'] .= '|different:current_password';
        }

        return $rules;
    }
}