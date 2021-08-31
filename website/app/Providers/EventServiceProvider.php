<?php

namespace App\Providers;

use App\Listeners\DeleteModelsRelatedToUser;
use App\Listeners\GenerateProfileHeaderColors;
use App\Listeners\UpdateChannelSeoFields;
use Common\Admin\Appearance\Events\AppearanceSettingSaved;
use Common\Auth\Events\UserAvatarChanged;
use Common\Auth\Events\UsersDeleted;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        AppearanceSettingSaved::class => [
            UpdateChannelSeoFields::class,
        ],
        UserAvatarChanged::class => [
            GenerateProfileHeaderColors::class,
        ],
        UsersDeleted::class => [
            DeleteModelsRelatedToUser::class,
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
