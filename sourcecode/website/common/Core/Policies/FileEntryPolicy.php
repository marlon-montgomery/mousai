<?php

namespace Common\Core\Policies;

use App\User;
use Arr;
use Common\Files\FileEntry;
use Common\Files\FileEntryUser;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Sanctum\Sanctum;
use Request;

class FileEntryPolicy
{
    use HandlesAuthorization;

    public function index(User $user, array $entryIds = null, int $userId = null): bool
    {
        if ($entryIds) {
            return $this->userCan($user, 'files.view', $entryIds);
        } else {
            return $user->hasPermission('files.view') || $userId === $user->id;
        }
    }

    public function show(?User $user, FileEntry $entry): bool
    {
        $token = $this->getAccessTokenFromRequest();

        if ($token) {
            if ($entry->preview_token === $token) {
                return true;
            } else if ($accessToken = app(PersonalAccessToken::class)->findToken($token)) {
                $user = $accessToken->tokenable;
            }
        }

        return $user && $this->userCan($user, 'files.view', $entry);
    }

    public function download(User $user, $entries): bool
    {
        $token = $this->getAccessTokenFromRequest();
        if ($token) {
            $previewTokenMatches = collect($entries)->every(function($entry) use($token) {
                return $entry['preview_token'] === $token;
            });
            if ($previewTokenMatches) {
                return true;
            } else if ($accessToken = app(PersonalAccessToken::class)->findToken($token)) {
                $user = $accessToken->tokenable;
            }
        }

        return $this->userCan($user, 'files.download', $entries);
    }

    public function store(User $user, int $parentId = null): bool
    {
        //check if user can modify parent entry (if specified)
        if ($parentId) {
            return $this->userCan($user, 'files.update', [$parentId]);
        }

        return $user->hasPermission('files.create');
    }

    /**
     * @param User $user
     * @param Collection|array|FileEntry $entries
     * @return bool
     */
    public function update(User $user, $entries)
    {
        return $this->userCan($user, 'files.update', $entries);
    }

    /**
     * @param User $user
     * @param Collection|array|FileEntry $entries
     * @return bool
     */
    public function destroy(User $user, $entries)
    {
        return $this->userCan($user, 'files.delete', $entries);
    }

    /**
     * @param User $currentUser
     * @param string $permission
     * @param FileEntry|array|Collection $entries
     * @return bool
     */
    protected function userCan(User $currentUser, string $permission, $entries)
    {
        if ($currentUser->hasPermission($permission)) {
            return true;
        }

        $entries = $this->findEntries($entries);

        // extending class might use "findEntries" method so we load users here
        if ( ! $entries->every->relationLoaded('users')) {
            $entries->load(['users' => function (MorphToMany $builder) use($currentUser) {
                $builder->where('users.id', $currentUser->id);
            }]);
        }

        return $entries->every(function(FileEntry $entry) use($permission, $currentUser) {
            $user = $entry->users->find($currentUser->id);
            return $this->userOwnsEntryOrWasGrantedPermission($user, $permission);
        });
    }

    /**
     * @param null|array|FileEntryUser $user
     * @param string $permission
     * @return bool
     */
    public function userOwnsEntryOrWasGrantedPermission($user, string $permission)
    {
        return $user && ($user['owns_entry'] || Arr::get($user['entry_permissions'], $this->sharedFilePermission($permission)));
    }

    /**
     * @param FileEntry|array|Collection $entries
     * @return Collection
     */
    protected function findEntries($entries)
    {
        if ($entries instanceof FileEntry) {
            return $entries->newCollection([$entries]);
        } else if (isset($entries[0]) && is_numeric($entries[0])) {
            return app(FileEntry::class)
                ->whereIn('id', $entries)
                ->get();
        } else {
            return $entries;
        }
    }

    protected function sharedFilePermission($fullPermission): string
    {
        switch ($fullPermission) {
            case 'files.view':
                return 'view';
            case 'files.update':
                return 'edit';
            case 'files.delete';
                return 'delete';
            case 'files.download';
                return 'download';
        }
    }

    protected function getAccessTokenFromRequest(): ?string {
        if ($token = request()->bearerToken()) {
            return $token;
        } else if ($token = request()->get('preview_token')) {
            return $token;
        } else if ($token = request()->get('accessToken')) {
            return $token;
        } else {
            return null;
        }
    }
}
