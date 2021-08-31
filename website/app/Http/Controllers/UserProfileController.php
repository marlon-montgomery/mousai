<?php

namespace App\Http\Controllers;

use App\User;
use App\UserProfile;
use Auth;
use Common\Core\BaseController;
use Illuminate\Http\Request;

class UserProfileController extends BaseController
{
    /**
     * @var Request
     */
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function show(User $user)
    {
        $relations = array_merge(
            array_filter(explode(',', $this->request->get('with', ''))),
            ['profile', 'links'],
        );
        $loadCount = array_merge(
            array_filter(explode(',', $this->request->get('withCount', ''))),
            ['followers', 'followedUsers']
        );

        $user->load($relations)
            ->loadCount($loadCount)
            ->setGravatarSize(220);

        if ( ! $user->getRelation('profile')) {
            $user->setRelation('profile', new UserProfile([
                'header_colors' => ['#a5d6a7', '#90caf9']
            ]));
        }

        $this->authorize('show', $user);

        $options = [
            'prerender' => [
                'view' => 'user.show',
                'config' => 'user.show'
            ]
        ];

        return $this->success([
            'user' => $user,
        ], 200, $options);
    }

    public function update()
    {
        $user = Auth::user();
        $this->authorize('update', $user);
        $user->fill($this->request->get('user'))->save();
        $profileData = $this->request->get('profile');

        $profile = $user->profile()->updateOrCreate(['user_id' => $user->id], $profileData);

        $user->links()->delete();
        $links = $user->links()->createMany($this->request->get('links'));

        $user->setRelation('profile', $profile);
        $user->setRelation('links', $links);

        return $this->success(['user' => $user]);
    }
}
