<?php

return [

    // URL and access
    'path' => env('ADMIN_PATH', 'admin'),
    'domain' => env('ADMIN_DOMAIN', null),
    'guard' => env('ADMIN_GUARD', 'admin'),
    'middleware' => ['web', 'auth:admin'],

    // Frontend engine
    'frontend' => env('ADMIN_FRONTEND', 'blade'),

    // Features
    'features' => [
        'audit_log' => env('ADMIN_AUDIT_LOG', true),
        'media' => env('ADMIN_MEDIA', true),
        'ai_search' => env('ADMIN_AI_SEARCH', true),
        'api_mode' => env('ADMIN_API_MODE', false),
        'multi_tenant' => env('ADMIN_MULTI_TENANT', false),
        'dark_mode' => env('ADMIN_DARK_MODE', true),
        'impersonation' => env('ADMIN_IMPERSONATION', false),
    ],

    // Branding
    'brand' => [
        'name' => env('ADMIN_BRAND_NAME', 'LibAdmin'),
        'logo' => env('ADMIN_BRAND_LOGO', null),
        'favicon' => env('ADMIN_BRAND_FAVICON', null),
        'login_bg' => env('ADMIN_BRAND_LOGIN_BG', null),
        'color' => env('ADMIN_BRAND_COLOR', '#d4537e'),
    ],

    // Theme
    'theme' => [
        'mode' => env('ADMIN_THEME_MODE', 'auto'),
        'primary' => env('ADMIN_THEME_PRIMARY', '#d4537e'),
        'sidebar' => env('ADMIN_THEME_SIDEBAR', 'dark'),
        'font' => env('ADMIN_THEME_FONT', 'Geist'),
        'radius' => env('ADMIN_THEME_RADIUS', 'md'),
        'density' => env('ADMIN_THEME_DENSITY', 'comfortable'),
    ],

    // Pagination
    'pagination' => [
        'default' => env('ADMIN_PAGINATION_DEFAULT', 25),
        'options' => [10, 25, 50, 100],
    ],

    // Media
    'media' => [
        'disk' => env('ADMIN_MEDIA_DISK', 'public'),
        'path' => env('ADMIN_MEDIA_PATH', 'media'),
        'max_file_size' => env('ADMIN_MEDIA_MAX_FILE_SIZE', '10mb'),
        'image_resize' => [1920, 1080],
        'thumbnail' => [200, 200],
        'generate_webp' => env('ADMIN_MEDIA_GENERATE_WEBP', true),
    ],

    // Audit log
    'audit' => [
        'retain_days' => env('ADMIN_AUDIT_RETAIN_DAYS', 365),
        'exclude_fields' => ['updated_at', 'remember_token'],
    ],

    // REST API
    'api' => [
        'prefix' => env('ADMIN_API_PREFIX', 'admin/api'),
        'auth' => env('ADMIN_API_AUTH', 'sanctum'),
        'versioning' => env('ADMIN_API_VERSIONING', true),
        'pagination' => env('ADMIN_API_PAGINATION', 25),
        'rate_limit' => env('ADMIN_API_RATE_LIMIT', '60/minute'),
    ],

    // Multi-tenancy
    'tenant_switcher' => [
        'visible_to' => ['superadmin'],
    ],

    // AI search
    'ai_search' => [
        'enabled' => env('ADMIN_AI_SEARCH_ENABLED', true),
        'show_sql' => env('APP_DEBUG', false),
        'max_resources' => 5,
    ],

];
