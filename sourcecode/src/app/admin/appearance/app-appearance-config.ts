import {HomepageAppearancePanelComponent} from './homepage-appearance-panel/homepage-appearance-panel.component';

export const APP_APPEARANCE_CONFIG = {
    defaultRoute: '/',
    navigationRoutes: [
        '/',
        'artist',
        'album',
        'track',
        'playlist',
        'genre',
        'user',
        'account',
        'login',
        'register',
    ],
    menus: {
        availableRoutes: [
            'admin/upload',
            'upload',
            'library/songs',
            'library/albums',
            'library/artists',
            'library/history',
        ],
        positions: [
            'sidebar-primary',
            'sidebar-secondary',
            'mobile-bottom',
            'landing-page-navbar',
            'landing-page-footer',
            'custom-page-navbar',
            'admin-navbar',
        ],
    },
    sections: [
        {
            name: 'landing page',
            component: HomepageAppearancePanelComponent,
            position: 1,
        }
    ]
};
