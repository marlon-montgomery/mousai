<?php namespace App\Traits;

use App;
use Common\Settings\Settings;
use Illuminate\Database\Eloquent\Builder;

trait OrdersByPopularity {

    public function scopeOrderByPopularity(Builder $query, $direction = 'desc')
    {
        $table = $this->getTable();
        $method = $table === 'playlists' || $table === 'genres' ?
            'local' :
            App::make(Settings::class)->get('player.sort_method', 'external');

        $column = $method === 'external' ? 'spotify_popularity' : $this->getLocalField($table);

        return $query->orderBy($column, $direction)
            ->orderBy($this->getTable().'.id', 'desc');
    }

    private function getLocalField(string $table): string
    {
       if ($table === 'genres') {
            return 'popularity';
        } else {
            return 'plays';
        }
    }
}
