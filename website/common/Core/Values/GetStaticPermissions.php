<?php

namespace Common\Core\Values;

use Common\Settings\Settings;
use Illuminate\Filesystem\Filesystem;
use Arr;
use Str;

class GetStaticPermissions
{
    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @param Filesystem $fs
     */
    public function __construct(Filesystem $fs)
    {
        $this->fs = $fs;
    }

    public function execute()
    {
        $permissions = array_merge_recursive(
            $this->fs->getRequire(app('path.common') . '/resources/defaults/permissions.php'),
            $this->fs->getRequire(resource_path('defaults/permissions.php'))
        )['all'];

        $compiled = [];
        foreach ($permissions as $key => $permissionGroup) {
            // format permissions and add generic description, if needed
            $compiled[$key] = collect($permissionGroup)->map(function($item) {
                if ( ! is_array($item)) {
                    $item = ['name' => $item];
                }

                if ( ! Arr::get($item, 'display_name')) {
                    $item['display_name'] = $this->getDisplayName($item['name']);
                }

                if ( ! Arr::get($item, 'description')) {
                    $item['description'] = $this->getGenericDescription($item['name']);
                }

                return $item;
            });
        }

        // remove billing permissions, if billing functionality is disabled
        if (isset($compiled['billing_plans']) && ! app(Settings::class)->get('billing.enable')) {
            unset($compiled['billing_plans']);
        }

        return $compiled;
    }

    private function getDisplayName($original)
    {
        // files.create => Create Files
        if ( ! \Str::contains($original, '.')) return $original;
        list($resource, $action) = explode('.', $original);
        return ucfirst($action) . ' ' . ucwords(str_replace('_', ' ', $resource));
    }

    /**
     * @param string $permission
     * @return string|null
     */
    private function getGenericDescription($permission)
    {
        if ( ! Str::contains($permission, '.')) return null;

        list($resource, $action) = explode('.', $permission);
        $pluralAction = Str::plural(str_replace('_', ' ', $resource));
        $verb = $this->getGenericVerb($action, $resource);

        return "Allow $verb $pluralAction.";
    }

    /**
     * @param string $action
     * @param string $resource
     * @return string|null
     */
    private function getGenericVerb($action, $resource)
    {
        if ($resource === 'file' && $action === 'create') {
            return 'uploading new';
        }

        switch ($action) {
            case 'view':
                return 'viewing ALL';
            case 'create':
                return 'creating new';
            case 'update':
                return 'updating ALL';
            case 'delete':
                return 'deleting ALL';
            case 'download':
                return 'downloading ALL';
            default:
                return null;
        }
    }
}
