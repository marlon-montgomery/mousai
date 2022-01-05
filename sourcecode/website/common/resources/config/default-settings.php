<?php

return [
    // dates
    ['name' => 'dates.format', 'value' => 'yyyy-MM-dd'],
    ['name' => 'dates.locale', 'value' => 'en_US'],

    // social login
    ['name' => 'social.google.enable', 'value' => true],
    ['name' => 'social.twitter.enable', 'value' => true],
    ['name' => 'social.facebook.enable', 'value' => true],

    // real time
    ['name' => 'realtime.enable', 'value' => false],

    // temp
    ['name' => 'registration.disable', 'value' => false],

    // cache
    ['name' => 'cache.report_minutes', 'value' => 60],

    // branding
    ['name' => 'branding.favicon', 'value' => 'client/favicon/icon-144x144.png'],

    // logos
    ['name' => 'branding.logo_dark', 'value' => 'client/assets/images/logo-dark.png'],
    ['name' => 'branding.logo_light', 'value' => 'client/assets/images/logo-light.png'],

    // translations
    ['name' => 'i18n.default_localization', 'value' => 'en'],
    ['name' => 'i18n.enable', 'value' => true],

    // sentry
    ['name' => 'logging.sentry_public', 'value' => null],

    // pusher
    ['name' => 'realtime.pusher_key', 'value' => null],

    // menus
    ['name' => 'menus', 'value' => json_encode([])],

    // homepage
    ['name' => 'homepage.type', 'value' => 'default'],

    // themes
    ['name' => 'themes.default_mode', 'value' => 'light'],
    ['name' => 'themes.user_change', 'value' => true],

    // billing
    ['name' => 'billing.enable', 'value' => false],
    ['name' => 'billing.paypal_test_mode', 'value' => true],
    ['name' => 'billing.stripe_test_mode', 'value' => true],
    ['name' => 'billing.stripe.enable', 'value' => false],
    ['name' => 'billing.paypal.enable', 'value' => false],
    ['name' => 'billing.accepted_cards', 'value' => json_encode(['visa', 'mastercard', 'american-express', 'discover'])],

    // custom domains
    ['name' => 'custom_domains.default_host', 'value' => null],

    // uploading
    ['name' => 'uploads.chunk', 'value' => true],
    ['name' => 'uploads.chunk_size', 'value' => 5242880],

    // GDPR
    ['name' => 'cookie_notice.enable', 'value' => true],
    ['name' => 'cookie_notice.position', 'value' => 'bottom'],
];
