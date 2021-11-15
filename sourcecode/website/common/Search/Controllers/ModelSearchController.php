<?php

namespace Common\Search\Controllers;

use Common\Core\BaseController;
use Illuminate\Database\Eloquent\Model;

class ModelSearchController extends BaseController
{
    public function index()
    {
        $namespace = modelTypeToNamespace(request('modelType'));
        $query = request('query');

        $this->authorize('index', $namespace);

        $results = app($namespace)
            ->search($query)
            ->take(15)
            ->get()
            ->map(function (Model $model) {
                return $model->toNormalizedArray();
            });

        return $this->success(['results' => $results]);
    }
}
