<?php

return [
    'remote_file_visibility' => env('REMOTE_FILE_VISIBILITY', 'public'),
    'use_presigned_s3_urls' => env('USE_PRESIGNED_S3_URLS', true),
    'static_file_delivery' => env('STATIC_FILE_DELIVERY', null),
    'uploads_disk_driver' => env('UPLOADS_DISK_DRIVER', 'local'),
    'public_disk_driver' => env('PUBLIC_DISK_DRIVER', 'local'),
    'file_preview_endpoint' => env('FILE_PREVIEW_ENDPOINT'),
    'version' => env('APP_VERSION'),
    'demo'    => env('IS_DEMO_SITE', false),
    'disable_update_auth' => env('DISABLE_UPDATE_AUTH', false),
    'enable_contact_page' => env('ENABLE_CONTACT_PAGE', false),
    'billing_integrated' => env('BILLING_ENABLED', false),
    'workspaces_integrated' => env('WORKSPACES_ENABLED', false),
    // TODO: refactor bedrive and remove
    'new_workspace_filter' => env('NEW_WORKSPACE_FILTER', false),
    'notifications_integrated' => env('NOTIFICATIONS_ENABLED', false),
    'notif_subs_integrated' => env('NOTIF_SUBS_ENABLED', false),
    'api_integrated' => env('API_INTEGRATED', false),
    'enable_custom_domains' => env('ENABLE_CUSTOM_DOMAINS', false),
    'dynamic_app_url' => env('DYNAMIC_APP_URL', true),
    'hide_docs_buttons' => env('HIDE_DOCS_BUTTONS', false),
    'verify_paypal_webhook' => env('VERIFY_PAYPAL_WEBHOOK', false),
    'trust_all_proxies' => env('TRUST_ALL_PROXIES', false),
    'has_mobile_app' => env('HAS_MOBILE_APP', false),
    'scout_mysql_mode' => env('SCOUT_MYSQL_MODE', 'extended'),
];
