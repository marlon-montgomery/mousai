<?php

namespace Common\Auth\Jobs;

use App\User;
use Common\Csv\BaseCsvExportJob;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class ExportUsersCsv extends BaseCsvExportJob
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int
     */
    protected $requesterId;

    public function __construct(int $requesterId)
    {
        $this->requesterId = $requesterId;
    }

    public function cacheName(): string
    {
        return 'users';
    }

    protected function generateLines()
    {
        $selectCols = [
            'id',
            'email',
            'username',
            'first_name',
            'last_name',
            'avatar',
            'created_at',
            'language',
            'country',
            'timezone',
        ];

        User::select($selectCols)->chunkById(100, function (Collection $chunk) {
            $chunk->each(function (User $user) {
                $data = $user->toArray();
                unset($data['display_name'], $data['has_password']);
                $this->writeLineToCsv($data);
            });
        });
    }
}
