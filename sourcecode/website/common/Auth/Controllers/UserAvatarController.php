<?php namespace Common\Auth\Controllers;

use Common\Auth\Events\UserAvatarChanged;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\JsonResponse;
use Storage;
use App\User;
use Illuminate\Http\Request;
use Common\Core\BaseController;

class UserAvatarController extends BaseController {

    /**
     * @var Request
     */
    private $request;

    /**
     * @var User
     */
    private $user;

    /**
     * @var FilesystemAdapter
     */
    private $storage;

    public function __construct(Request $request, User $user)
    {
        $this->request = $request;
        $this->storage = Storage::disk('public');
        $this->user = $user;
    }

    public function store(int $userId) {

        $user = $this->user->findOrFail($userId);

        $this->authorize('update', $user);

        $this->validate($this->request, [
            'file' => 'required|image|max:1500',
        ]);

        // delete old user avatar
        $this->storage->delete($user->getRawOriginal('avatar'));

        // store new avatar on public disk
        $path = $this->request->file('file')
            ->storePublicly('avatars', ['disk' => 'public']);

        // attach avatar to user model
        $user->avatar = $path;
        $user->save();

        event(new UserAvatarChanged($user));

        return $this->success([
            'user' => $user,
            'fileEntry' => ['url' => Storage::disk('public')->url($path)]
        ]);
    }

    /**
     * @param int $userId
     * @return User
     */
    public function destroy($userId)
    {
        $user = $this->user->findOrFail($userId);

        $this->authorize('update', $user);

        $this->storage->delete($user->getRawOriginal('avatar'));

        $user->avatar = null;
        $user->save();

        event(new UserAvatarChanged($user));

        return $user;
    }
}
