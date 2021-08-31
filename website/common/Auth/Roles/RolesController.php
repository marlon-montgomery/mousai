<?php namespace Common\Auth\Roles;

use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Common\Core\BaseController;

class RolesController extends BaseController
{
    /**
     * @var User
     */
    private $user;

    /**
     * @var Role
     */
    private $role;

    /**
     * @var Request
     */
    private $request;

    public function __construct(Request $request, Role $role, User $user)
    {
        $this->role = $role;
        $this->user = $user;
        $this->request = $request;
    }

    /**
     * @return JsonResponse
     */
    public function index()
    {
        $this->authorize('index', Role::class);

        $pagination = $this->role->with('permissions')->paginate(13);

        return $this->success(['pagination' => $pagination]);
    }

    /**
     * @return JsonResponse
     */
    public function store()
    {
        $this->authorize('store', Role::class);

        $this->validate($this->request, [
            'name'        => 'required|unique:roles|min:2|max:255',
            'default'     => 'nullable|boolean',
            'guests'      => 'nullable|boolean',
            'permissions' => 'nullable|array'
        ]);

        $role = app(CrupdateRole::class)
            ->execute($this->request->all());

        return $this->success(['data' => $role], 201);
    }

    /**
     * @param integer $id
     * @return JsonResponse
     */
    public function update($id)
    {
        $this->authorize('update', Role::class);

        $this->validate($this->request, [
            'name'        => "min:2|max:255|unique:roles,name,$id",
            'default'     => 'boolean',
            'guests'      => 'boolean',
            'permissions' => 'array'
        ]);

        $role = $this->role->findOrFail($id);

        $role = app(CrupdateRole::class)
            ->execute($this->request->all(), $role);

        return $this->success(['data' => $role]);
    }

    /**
     * Delete role matching given id.
     *
     * @param integer $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        $role = $this->role->findOrFail($id);

        $this->authorize('destroy', $role);

        $role->users()->detach();
        $role->delete();

        return $this->success([], 204);
    }

    /**
     * Add given users to role.
     *
     * @param integer $roleId
     * @return JsonResponse
     */
    public function addUsers($roleId)
    {
        $this->authorize('update', Role::class);

        $this->validate($this->request, [
            'emails'   => 'required|array|min:1|max:25',
            'emails.*' => 'required|email|max:255'
        ], [
            'emails.*.email'   => 'Email address must be valid.',
            'emails.*.required' => 'Email address is required.',
        ]);

        $role = $this->role->findOrFail($roleId);

        $users = $this->user->with('roles')->whereIn('email', $this->request->get('emails'))->get(['email', 'id']);

        if ($users->isEmpty()) {
            return $this->error(__('Could not attach specified users to role.'), [], 422);
        }

        //filter out users that are already attached to this role
        $users = $users->filter(function($user) use($roleId) {
            return ! $user->roles->contains('id', (int) $roleId);
        });

        $role->users()->attach($users->pluck('id')->toArray());

        return $this->success(['data' => $users]);
    }

    /**
     * Remove given users from role.
     *
     * @param integer $roleId
     * @return JsonResponse
     */
    public function removeUsers($roleId)
    {
        $this->authorize('update', Role::class);

        $this->validate($this->request, [
            'ids'   => 'required|array|min:1',
            'ids.*' => 'required|integer'
        ]);

        $role = $this->role->findOrFail($roleId);

        $role->users()->detach($this->request->get('ids'));

        return $this->success(['data' => $this->request->get('ids')]);
    }
}
