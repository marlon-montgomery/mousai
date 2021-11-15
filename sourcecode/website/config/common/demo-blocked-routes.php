<?php

return [
    //backstage
    ['method' => 'POST', 'name' => 'backstage-request/{backstageRequest}/approve'],
    ['method' => 'POST', 'name' => 'backstage-request/{backstageRequest}/deny'],

    //artists
    ['method' => 'DELETE', 'name' => 'artists'],
    ['method' => 'PUT', 'name' => 'artists/{artist}'],
    ['method' => 'POST', 'name' => 'artists'],

    //albums
    ['method' => 'DELETE', 'name' => 'albums'],
    ['method' => 'PUT', 'name' => 'albums/{album}'],
    ['method' => 'POST', 'name' => 'albums'],

    //tracks
    ['method' => 'DELETE', 'name' => 'tracks'],
    ['method' => 'PUT', 'name' => 'tracks/{id}'],
    ['method' => 'POST', 'name' => 'tracks'],

    //lyrics
    ['method' => 'DELETE', 'name' => 'lyrics'],
    ['method' => 'PUT', 'name' => 'lyrics/{id}'],
    ['method' => 'POST', 'name' => 'lyrics'],

    //comments
    ['method' => 'DELETE', 'name' => 'comment'],
    ['method' => 'PUT', 'name' => 'comment/{comment}'],
    ['method' => 'POST', 'name' => 'comments'],

    //playlists
    ['method' => 'DELETE', 'name' => 'playlists'],

    //sitemap
    ['method' => 'POST', 'name' => 'admin/sitemap/generate'],

    //upload tracks
    ['method' => 'POST', 'name' => 'uploads/videos'],

    // Genres
    ['method' => 'DELETE', 'name' => 'genres'],
    ['method' => 'PUT', 'name' => 'genres/{id}'],
    ['method' => 'POST', 'name' => 'genres'],

    // Channels
    ['method' => 'POST', 'name' => 'channel/{channel}/detach-item'],
    ['method' => 'POST', 'name' => 'channel/{channel}/attach-item'],
    ['method' => 'POST', 'name' => 'channel/{channel}/change-order'],
    ['method' => 'POST', 'name' => 'channel'],
    ['method' => 'PUT', 'name' => 'channel/{channel}'],
    ['method' => 'DELETE', 'name' => 'channel/{channel}'],
];
