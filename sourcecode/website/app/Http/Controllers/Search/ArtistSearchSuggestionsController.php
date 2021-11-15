<?php

namespace App\Http\Controllers\Search;

use App\Artist;
use App\Track;
use Auth;
use Common\Core\BaseController;

class ArtistSearchSuggestionsController extends BaseController
{
    public function index()
    {
        $this->authorize("index", Artist::class);

        $limit = request("limit", 10);
        $query = request("query");
        $user = Auth::user();

        $shouldListAll =
            $user->hasPermission("music.update") ||
            $user->getRestrictionValue("music.create", "artist_selection") ||
            request()->has("listAll");

        $builder = $shouldListAll ? Artist::query() : $user->artists();

        $artists = $builder
            ->where("name", "like", $query . "%")
            ->limit($limit)
            ->get(["artists.id", "name", "image_small"]);

        $alreadyHasCurrentUser = $artists->first(function (Artist $artist) {
            return $artist->pivot &&
                $artist->pivot->user_id === Auth::user()->id;
        });

        // don't show current user artist placeholder on backstage "claim artist" page
        if (!$alreadyHasCurrentUser && !request()->has("listAll")) {
            $artists->prepend(
                (object) [
                    "id" => "CURRENT_USER",
                    "name" => Auth::user()->display_name,
                    "image_small" => Auth::user()->avatar,
                    "model_type" => Artist::MODEL_TYPE,
                ],
            );
        }

        return $this->success(["artists" => $artists]);
    }
}
