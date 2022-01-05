<?php

namespace App\Console\Commands;

use App\Channel;
use App\Actions\Channel\UpdateChannelContent;
use Illuminate\Console\Command;

class UpdateAllChannelsContent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'channels:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        app(Channel::class)
            ->whereNotNull('auto_update')
            ->limit(20)
            ->get()
            ->each(function(Channel $channel) {
                app(UpdateChannelContent::class)->execute($channel);
            });
    }
}
