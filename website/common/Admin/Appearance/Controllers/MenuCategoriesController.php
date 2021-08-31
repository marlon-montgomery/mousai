<?php

namespace Common\Admin\Appearance\Controllers;

use Common\Core\BaseController;

class MenuCategoriesController extends BaseController
{
    public function __construct()
    {
        $this->middleware('isAdmin');
    }

    public function index()
    {
        $categories  = array_map(function($category) {
            $category['items'] = app($category['itemsLoader'])->execute();
            unset($category['itemsLoader']);
            return $category;
        }, config('common.menus'));

        return $this->success(['categories' => $categories]);
    }
}