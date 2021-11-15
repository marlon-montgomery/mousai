<?php

return [
    //homepage
    ['name' => 'homepage.type', 'value' => 'Channels'],
    ['name' => 'homepage.value', 'value' => 5],

    //cache
    ['name' => 'cache.report_minutes', 'value' => 60],
    ['name' => 'cache.homepage_days', 'value' => 1],
    ['name' => 'automation.artist_interval', 'value' => 7],

    //providers
    ['name' => 'artist_provider', 'value' => 'local'],
    ['name' => 'album_provider', 'value' => 'local'],
    ['name' => 'radio_provider', 'value' => 'spotify'],
    ['name' => 'genres_provider', 'value' => 'local'],
    ['name' => 'search_provider', 'value' => 'local'],
    ['name' => 'artist_bio_provider', 'value' => 'wikipedia'],
    ['name' => 'wikipedia_language', 'value' => 'en'],
    ['name' => 'providers.lyrics', 'value' => 'lyricswikia'],

    //player
    ['name' => 'youtube.suggested_quality', 'value' => 'default'],
    ['name' => 'youtube.region_code', 'value' => 'us'],
    ['name' => 'youtube.search_method', 'value' => 'site'],
    ['name' => 'youtube.store_id', 'value' => false],
    ['name' => 'player.default_volume', 'value' => 30],
    ['name' => 'player.hide_queue', 'value' => 0],
    ['name' => 'player.hide_video', 'value' => 0],
    ['name' => 'player.hide_video_button', 'value' => 0],
    ['name' => 'player.hide_lyrics', 'value' => 0],
    ['name' => 'player.mobile.auto_open_overlay', 'value' => 1],
    ['name' => 'player.enable_download', 'value' => 0],
    ['name' => 'player.sort_method', 'value' => 'external'],
    ['name' => 'player.seekbar_type', 'value' => 'line'],
    ['name' => 'player.track_comments', 'value' => false],
    ['name' => 'player.show_upload_btn', 'value' => false],
    ['name' => 'uploads.autoMatch', 'value' => true],
    ['name' => 'player.default_artist_view', 'value' => 'list'],
    ['name' => 'player.enable_repost', 'value' => false],
    ['name' => 'artistPage.tabs', 'value' => json_encode([
        ['id' => 1, 'active' => true],
        ['id' => 2, 'active' => true],
        ['id' => 3, 'active' => true],
        ['id' => 4, 'active' => false],
        ['id' => 5, 'active' => false],
        ['id' => 6, 'active' => false],
    ])],

    //other
    ['name' => 'https.enable_cert_verification', 'value' => 1],
    ['name' => 'site.force_https', 'value' => 0],

    //menus
    ['name' => 'menus', 'value' => json_encode([
        ['name' => 'Primary', 'position' => 'sidebar-primary', 'items' => [
            ['type' => 'route', 'order' => 1, 'label' => 'Popular Albums', 'action' => '/popular-albums', 'icon' => 'album'],
            ['type' => 'route', 'order' => 2, 'label' => 'Genres', 'action' => '/genres', 'icon' => 'local-offer'],
            ['type' => 'route', 'order' => 3, 'label' => 'Popular Tracks', 'action' => '/popular-tracks', 'icon' => 'trending-up'],
            ['type' => 'route', 'order' => 4, 'label' => 'New Releases', 'action' => '/new-releases', 'icon' => 'new-releases']
        ]],
        ['name' => 'Secondary ', 'position' => 'sidebar-secondary', 'items' => [
            ['type' => 'route', 'order' => 1, 'label' => 'Songs', 'action' => '/library/songs', 'icon' => 'audiotrack'],
            ['type' => 'route', 'order' => 2, 'label' => 'Albums', 'action' => '/library/albums', 'icon' => 'album'],
            ['type' => 'route', 'order' => 3, 'label' => 'Artists', 'action' => '/library/artists', 'icon' => 'mic'],
            ['type' => 'route', 'order' => 3, 'label' => 'History', 'action' => '/library/history', 'icon' => 'history'],
        ]],
        ['name' => 'Mobile ', 'position' => 'mobile-bottom', 'items' => [
            ['type' => 'route', 'order' => 1, 'label' => 'Genres', 'action' => '/genres', 'icon' => 'local-offer'],
            ['type' => 'route', 'order' => 2, 'label' => 'Top 50', 'action' => '/popular-tracks', 'icon' => 'trending-up'],
            ['type' => 'route', 'order' => 3, 'label' => 'Search', 'action' => '/search', 'icon' => 'search'],
            ['type' => 'route', 'order' => 4, 'label' => 'Your Music', 'action' => '/library', 'icon' => 'library-music'],
        ]]
    ])],

    // LANDING PAGE
    ['name' => 'homepage.appearance', 'value' => json_encode([
        'headerTitle' => 'Connect on BeMusic',
        'headerSubtitle' => 'Discover, stream, and share a constantly expanding mix of music from emerging and major artists around the world.',
        'headerImage' => 'client/assets/images/landing/landing-header-bg.jpg',
        'headerOverlayColor1' => 'rgba(16,119,34,0.56)',
        'headerOverlayColor2' => 'rgba(42,148,71,1)',
        'footerTitle' => 'Make music? Create audio?',
        'footerSubtitle' => 'Get on BeMusic to help you connect with fans and grow your audience.',
        'footerImage' => 'client/assets/images/landing/landing-footer-bg.jpg',
        'actions' => [
            'inputText' => 'Search for artists, albums and tracks...',
            'inputButton' => 'Search',
            'cta1' => 'Signup Now',
            'cta2' => 'Explore',
        ],
        'primaryFeatures' => [],
        'secondaryFeatures' => [
            [
                'title' => 'Watch Anytime, Anywhere. From Any Device.',
                'subtitle' => 'Complete Freedom',
                'image' => 'client/assets/images/landing/landing-feature-1.jpg',
                'description' => 'Stream music in the browser, on Phone, Tablet, Smart TVs, Consoles, Chromecast, Apple TV and more.'
            ],
            [
                'title' => 'Get More From Bemusic With Pro',
                'subtitle' => 'BeMusic Pro',
                'image' => 'client/assets/images/landing/landing-feature-2.jpg',
                'description' => 'Subscribe to BeMusic pro to hide ads, increase upload time and get access to other exclusive features.'
            ]
        ],
        'channelIds' => [1],
    ])],
];
