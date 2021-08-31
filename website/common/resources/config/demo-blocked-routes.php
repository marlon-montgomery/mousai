<?php

return [
    // admin
    ['method' => 'POST', 'name' => 'settings'],
    ['method' => 'POST', 'name' => 'admin/appearance'],
    ['method' => 'POST', 'name' => 'cache/clear'],
    ['method' => 'POST', 'name' => 'artisan/call'],
    ['method' => 'POST', 'name' => 'admin/search/import'],
    ['method' => 'POST', 'name' => 'import-media/single-item'],

    // css theme
    ['method' => 'POST', 'name' => 'css-theme'],
    ['method' => 'PUT', 'name' => 'css-theme/{css_theme}'],
    ['method' => 'DELETE', 'name' => 'css-theme/{css_theme}'],

    // favicon
    ['method' => 'POST', 'name' => 'uploads/favicon'],

    // localizations
    ['method' => 'DELETE', 'name' => 'localizations/{id}'],
    ['method' => 'PUT', 'name' => 'localizations/{id}'],
    ['method' => 'POST', 'name' => 'localizations'],

    // pages
    ['method' => 'DELETE', 'name' => 'page/{page}', 'origin' => 'admin'],
    ['method' => 'PUT', 'name' => 'page/{page}', 'origin' => 'admin'],

    // billing plans
    ['method' => 'POST', 'name' => 'billing-plan'],
    ['method' => 'POST', 'name' => 'billing-plan/sync'],
    ['method' => 'PUT', 'name' => 'billing-plan/{billing_plan}'],
    ['method' => 'DELETE', 'name' => 'billing-plan/{billing_plan}'],

    // subscriptions
    ['method' => 'POST', 'origin' => 'admin', 'name' => 'billing/subscriptions'],
    ['method' => 'PUT', 'origin' => 'admin', 'name' => 'billing/subscriptions/{id}'],
    ['method' => 'DELETE', 'origin' => 'admin', 'name' => 'billing/subscriptions/{id}'],

    // users
    ['method' => 'POST', 'name' => 'users/{id}/password/change'],
    ['method' => 'PUT', 'origin' => 'admin', 'name' => 'users/{user}'],
    ['method' => 'POST', 'origin' => 'admin', 'name' => 'users'],
    ['method' => 'DELETE', 'name' => 'users/{user}'],
    ['method' => 'POST', 'origin' => 'admin', 'name' => 'users/{id}/roles/attach'],
    ['method' => 'POST', 'origin' => 'admin', 'name' => 'users/{id}/roles/detach'],

    // tags
    ['method' => 'POST', 'origin' => 'admin', 'name' => 'tags'],
    ['method' => 'PUT', 'origin' => 'admin', 'name' => 'tags/{id}'],
    ['method' => 'DELETE', 'origin' => 'admin', 'name' => 'tags/{tagIds}'],

    // roles
    ['method' => 'DELETE', 'name' => 'roles/{id}'],
    ['method' => 'PUT', 'name' => 'roles/{id}'],
    ['method' => 'POST', 'name' => 'roles'],
    ['method' => 'POST', 'name' => 'roles/{id}/add-users'],
    ['method' => 'POST', 'name' => 'roles/{id}/remove-users'],

    // CUSTOM DOMAINS
    ['method' => 'DELETE', 'name' => 'custom-domain/{custom_domain}', 'origin' => 'admin'],
    ['method' => 'PUT', 'name' => 'custom-domain/{custom_domain}', 'origin' => 'admin'],

    // contact
    ['method' => 'POST', 'name' => 'contact-page'],

    // uploads
    ['method' => 'DELETE', 'name' => 'uploads', 'origin' => 'admin'],
];
