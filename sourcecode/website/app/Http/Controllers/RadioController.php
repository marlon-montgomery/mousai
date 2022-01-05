<?php

namespace App\Http\Controllers;

use App\Artist;
use App\Genre;
use App\Services\Providers\ProviderResolver;
use App\Track;
use Cache;
use Carbon\Carbon;
use Common\Core\BaseController;

class RadioController extends BaseController
{
    public function getRecommendations(string $type, int $seedId): array
    {
        $model = $this->findModel($type, $seedId);

        $this->authorize('show', $model);

        $recommendations = Cache::remember("radio.$type.$model->id", Carbon::now()->addDays(2), function() use($model, $type) {
            $recommendations = app(ProviderResolver::class)->get('radio')->getRecommendations($model, $type);
            return empty($recommendations) ? null : $recommendations;
        });

        return [
            'type' => $type,
            'seed' => $model,
            'recommendations' => $recommendations ?: [],
        ];
    }

    private function findModel(string $type, int $modelId)
    {
        if ($type === 'artist') {
            return Artist::findOrFail($modelId);
        } else if ($type === 'genre') {
            return Genre::findOrFail($modelId);
        } else if ($type === 'track') {
            return Track::with('album.artists')->findOrFail($modelId);
        }

        abort(404, 'Invalid radio seed');
    }
}
