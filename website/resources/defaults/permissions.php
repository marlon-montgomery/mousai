<?php

return [
    'roles' => [
        [
            'default' => true,
            'name' => 'users',
            'extends' => 'users',
            'permissions' => [
                'music.view',
                'users.view' ,
                'playlists.create',
                'localizations.view',
                'playlists.view',
                'uploads.create',
                'comments.create',
                'music.embed',
                'music.play',
           ]
        ],
        'guests' => [
            'guests' => true,
            'name' => 'guests',
            'extends' => 'guests',
            'permissions' => [
                'music.view',
                'users.view' ,
                'playlists.view',
                'music.embed',
                'music.play',
            ]
        ],
        'artists' => [
            'internal' => true,
            'artists' => true,
            'name' => 'artists',
            'description' => 'Role assigned to a user when their "become artist request" is approved.',
            'permissions' => [
                'music.create',
            ]
        ]
    ],
    'all' => [
        // MUSIC
        'music' => [
            [
                'name' => 'music.view',
                'description' => 'Allows viewing of music content on the site (tracks, albums, artists, channels etc.)',
            ],
            [
                'name' => 'music.play',
                'description' => 'Allows playback of music and video on the site.',
            ],
            [
                'name' => 'music.download',
                'description' => 'Allows download of music and video on the site.',
            ],
            [
                'name' => 'music.embed',
                'description' => 'Allows embedding of tracks, albums and playlists on external sites.',
            ],
            [
                'name' => 'music.create',
                'description' => 'Allows uploading and creating new tracks and albums on the site.',
                'restrictions' => [
                    [
                        'name' => 'minutes',
                        'type' => 'number',
                        'description' => 'How many minutes all user tracks are allowed to take up. Leave empty for unlimited.',
                    ],
                    [
                        'name' => 'artist_selection',
                        'type' => 'bool',
                        'description' => 'Allows attaching track or album to any artist that exists on the site, instead of only the ones managed by current user.',
                    ],
                ]
            ],
            [
                'name' => 'music.update',
                'description' => 'Allows editing all media (album, track, artist etc.) on the site, even if user did not create them initially. User can edit their own media without this permission.',
            ],
            [
                'name' => 'music.delete',
                'description' => 'Allows deleting any media item on the site, even if user did not create them initially. User can delete their own media without this permission.',
            ],
        ],

        'playlists' => [
            [
                'name' => 'playlists.view',
                'description' => 'Allow viewing and searching for playlists marked as public.',
            ],
            [
                'name' => 'playlists.create',
                'description' => 'Allow creating new playlists.',
            ],
            [
                'name' => 'playlists.update',
                'description' => 'Allow editing of all playlists, whether user is the owner or not. User can edit their own playlists without this permission.',
            ],
            [
                'name' => 'playlists.delete',
                'description' => 'Allow deleting any playlist, whether user is the owner or not. User can delete their own playlists without this permission.',
            ],
        ],

        'comments' => [
            [
                'name' => 'comments.view',
                'description' => 'Allow viewing a single comment or list of comments on the site.',
            ],
            [
                'name' => 'comments.create',
                'description' => 'Allow creating new comments.',
            ],
            [
                'name' => 'comments.update',
                'description' => 'Allow editing of all comments, whether user created that comment or not. User can edit their own comments without this permission.',
            ],
            [
                'name' => 'comments.delete',
                'description' => 'Allow deleting any comment, whether user created that comment or not. User can delete their own comments without this permission.',
            ],
        ],

        // LEGACY / ADVANCED

        'artists' => [
            [
                'name' => 'artists.view',
                'description' => 'Allow viewing artist page and searching for artists.',
                'advanced' => true,
            ],
            [
                'name' => 'artists.create',
                'description' => 'Allow creating new artists.',
                'advanced' => true,
            ],
            [
                'name' => 'artists.update',
                'description' => 'Allow editing of all artists, whether user has created them or not.',
                'advanced' => true,
            ],
            [
                'name' => 'artists.delete',
                'description' => 'Allow deleting any artist, whether user has created them or not.',
                'advanced' => true,
            ],
        ],

        'albums' => [
            [
                'name' => 'albums.view',
                'description' => 'Allow viewing album pages and searching for albums.',
                'advanced' => true,
            ],
            [
                'name' => 'albums.create',
                'description' => 'Allow creating new albums.',
                'advanced' => true,
            ],
            [
                'name' => 'albums.update',
                'description' => 'Allow editing of all albums, whether user has created them or not.',
                'advanced' => true,
            ],
            [
                'name' => 'albums.delete',
                'description' => 'Allow deleting any album, whether user has created them or not.',
                'advanced' => true,
            ],
        ],

        'tracks' => [
            [
                'name' => 'tracks.view',
                'description' => 'Allow viewing track page and searching for tracks.',
                'advanced' => true,
            ],
            [
                'name' => 'tracks.create',
                'description' => 'Allow creating new tracks.',
                'advanced' => true,
            ],
            [
                'name' => 'tracks.update',
                'description' => 'Allow editing of all tracks, whether user has created them or not.',
                'advanced' => true,
            ],
            [
                'name' => 'tracks.delete',
                'description' => 'Allow deleting any track, whether user has created them or not.',
                'advanced' => true,
            ],
        ],

        'genres' => [
            [
                'name' => 'genres.view',
                'description' => 'Allow viewing genre pages and searching for genres.',
                'advanced' => true,
            ],
            [
                'name' => 'genres.create',
                'description' => 'Allow creating new genres.',
                'advanced' => true,
            ],
            [
                'name' => 'genres.update',
                'description' => 'Allow editing of all genres, whether user has created them or not.',
                'advanced' => true,
            ],
            [
                'name' => 'genres.delete',
                'description' => 'Allow deleting any genre, whether user has created them or not.',
                'advanced' => true,
            ],
        ],

        'lyrics' => [
            [
                'name' => 'lyrics.view',
                'description' => 'Allow viewing and searching for lyrics.',
                'advanced' => true,
            ],
            [
                'name' => 'lyrics.create',
                'description' => 'Allow creating new lyrics.',
                'advanced' => true,
            ],
            [
                'name' => 'lyrics.update',
                'description' => 'Allow editing of all lyrics, whether user has created them or not.',
                'advanced' => true,
            ],
            [
                'name' => 'lyrics.delete',
                'description' => 'Allow deleting any lyric, whether user has created them or not.',
                'advanced' => true,
            ],
        ],

        'channels' => [
            [
                'name' => 'channels.view',
                'description' => 'Allow viewing channels on the site.',
                'advanced' => true,
            ],
            [
                'name' => 'channels.create',
                'description' => 'Allow creating new channels in admin area.',
                'advanced' => true,
            ],
            [
                'name' => 'channels.update',
                'description' => 'Allow editing of all channels in admin area.',
                'advanced' => true,
            ],
            [
                'name' => 'channels.delete',
                'description' => 'Allow deleting of all channels in admin area.',
                'advanced' => true,
            ],
        ],
    ]
];
