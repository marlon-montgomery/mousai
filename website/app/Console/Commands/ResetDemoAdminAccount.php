<?php namespace App\Console\Commands;

use Common\Auth\Permissions\Permission;
use Common\Localizations\Localization;
use DB;
use Hash;
use Artisan;
use App\User;
use App\Playlist;
use Illuminate\Console\Command;

class ResetDemoAdminAccount extends Command {

	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'demo:reset';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Reset admin account';

    /**
     * @var User
     */
    private $user;

    /**
     * @var Playlist
     */
    private $playlist;

    /**
     * @var Localization
     */
    private $localization;

    /**
     * ResetDemoAdminAccount constructor.
     *
     * @param User $user
     * @param Playlist $playlist
     * @param Localization $localization
     */
    public function __construct(User $user, Playlist $playlist, Localization $localization)
	{
        parent::__construct();

	    $this->user = $user;
        $this->playlist = $playlist;
        $this->localization = $localization;
    }

    /**
     * Execute the console command.
     *
     * @throws \Exception
     * @return void
     */
	public function handle()
	{
		$admin = $this->user->where('email', 'admin@admin.com')->firstOrFail();

        $adminPermission = app(Permission::class)->where('name', 'admin')->first();

        $admin->avatar = null;
        $admin->username = null;
        $admin->first_name = null;
        $admin->last_name = null;
        $admin->password = Hash::make('admin');
        $admin->permissions()->sync($adminPermission->id);
        $admin->save();

        $admin->likedTracks()->detach();
        $admin->likedAlbums()->detach();
        $admin->likedArtists()->detach();

        $ids = $admin->playlists()->where('owner_id', $admin->id)->select('playlists.id')->pluck('id');
        $this->playlist->whereIn('id', $ids)->delete();
        DB::table('playlist_track')->whereIn('playlist_id', $ids)->delete();
        DB::table('playlist_user')->whereIn('playlist_id', $ids)->delete();

        //delete localizations
        $this->localization->get()->each(function(Localization $localization) {
            if (strtolower($localization->name) !== 'english') {
                $localization->delete();
            }
        });

        Artisan::call('cache:clear');

        $this->info('Demo site reset.');
	}
}
