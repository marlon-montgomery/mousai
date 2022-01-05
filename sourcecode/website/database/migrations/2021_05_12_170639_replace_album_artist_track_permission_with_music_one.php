<?php

use Common\Auth\Permissions\Permission;
use Common\Database\Seeds\PermissionTableSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;

class ReplaceAlbumArtistTrackPermissionWithMusicOne extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        app(PermissionTableSeeder::class)->run();

        $permissions = Permission::get();

        $oldIds = $permissions->filter(function(Permission $permission) {
            return in_array($permission->group, ['artists', 'albums', 'tracks', 'lyrics', 'genres']);
        })->pluck('id');

        $cursor = DB::table('permissionables')->whereIn('permission_id', $oldIds)->cursor();
        foreach ($cursor as $row) {
            $original = $permissions->find($row->permission_id);

            if (Str::endsWith($original->name, 'view')) {
                $new = $permissions->where('name', 'music.view')->first();
            } else if (Str::endsWith($original->name, 'create')) {
                $new = $permissions->where('name', 'music.create')->first();
            } else if (Str::endsWith($original->name, 'update')) {
                $new = $permissions->where('name', 'music.update')->first();
            } else if (Str::endsWith($original->name, 'delete')) {
                $new = $permissions->where('name', 'music.delete')->first();
            } else if (Str::endsWith($original->name, 'embed')) {
                $new = $permissions->where('name', 'music.embed')->first();
            } else if (Str::endsWith($original->name, 'play')) {
                $new = $permissions->where('name', 'music.play')->first();
            } else if (Str::endsWith($original->name, 'download')) {
                $new = $permissions->where('name', 'music.download')->first();
            }

            try {
                DB::table('permissionables')->insert([
                    'permission_id' => $new->id,
                    'permissionable_id' => $row->permissionable_id,
                    'permissionable_type' => $row->permissionable_type,
                    'restrictions' => $row->restrictions,
                ]);
            } catch (QueryException $exception) {
                // catch duplicate exception
            }

            DB::table('permissionables')->where('id', $row->id)->delete();
        }

        $toDelete = $permissions->filter(function(Permission $permission) {
            return in_array($permission->name, ['tracks.embed', 'tracks.play', 'tracks.download', 'mail_templates.view', 'mail_templates.update']);
        })->pluck('id');
        DB::table('permissions')->whereIn('id', $toDelete)->delete();
        DB::table('permissionables')->whereIn('permission_id', $toDelete)->delete();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
