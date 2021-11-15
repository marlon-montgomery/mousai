<?php namespace Common\Database\Seeds;

use Common\Auth\Permissions\Permission;
use Common\Core\Values\GetStaticPermissions;
use Illuminate\Database\Seeder;

class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $allPermissions = app(GetStaticPermissions::class)->execute();
        $allPermissions['admin'][] = [
            'name' => 'admin',
            'display_name' => 'Super Admin',
            'description' => 'Give all permissions to user.',
            'group' => 'admin',
        ];

        foreach ($allPermissions as $groupName => $group) {
            foreach ($group as $permission) {
                $permission['group'] = $groupName;
                app(Permission::class)->updateOrCreate(['name' => $permission['name']], $permission);
            }
        }

        // delete legacy permissions
        app(Permission::class)->whereNull('group')->delete();
    }
}
