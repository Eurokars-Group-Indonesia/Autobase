<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains security-related configuration for the application.
    | These settings help protect against common web vulnerabilities.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Content Security Policy
    |--------------------------------------------------------------------------
    |
    | Define allowed sources for various content types. This helps prevent
    | XSS attacks by controlling what resources can be loaded.
    |
    */
    'csp' => [
        'enabled' => env('CSP_ENABLED', true),
        'report_only' => env('CSP_REPORT_ONLY', false),
        'directives' => [
            'default-src' => ["'self'"],
            'script-src' => [
                "'self'",
                "'unsafe-inline'", // Required for inline scripts (consider removing)
                'https://cdn.jsdelivr.net',
                'https://code.jquery.com',
                'https://fonts.googleapis.com',
            ],
            'style-src' => [
                "'self'",
                "'unsafe-inline'", // Required for inline styles (consider removing)
                'https://cdn.jsdelivr.net',
                'https://fonts.googleapis.com',
            ],
            'font-src' => [
                "'self'",
                'https://fonts.gstatic.com',
                'https://cdn.jsdelivr.net',
            ],
            'img-src' => [
                "'self'",
                'data:',
                'https:',
            ],
            'connect-src' => ["'self'"],
            'frame-ancestors' => ["'none'"], // Prevent iframe embedding
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Headers
    |--------------------------------------------------------------------------
    |
    | Configure security headers to be sent with every response.
    |
    */
    'headers' => [
        'x-frame-options' => 'DENY',
        'x-content-type-options' => 'nosniff',
        'x-xss-protection' => '1; mode=block',
        'referrer-policy' => 'strict-origin-when-cross-origin',
        'permissions-policy' => 'geolocation=(), microphone=(), camera=()',
    ],

    /*
    |--------------------------------------------------------------------------
    | HSTS (HTTP Strict Transport Security)
    |--------------------------------------------------------------------------
    |
    | Force HTTPS connections for the specified duration.
    |
    */
    'hsts' => [
        'enabled' => env('HSTS_ENABLED', true),
        'max_age' => 31536000, // 1 year
        'include_subdomains' => true,
        'preload' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Configure rate limiting for various endpoints.
    |
    */
    'rate_limiting' => [
        'login' => [
            'max_attempts' => 5,
            'decay_minutes' => 1,
        ],
        'api' => [
            'max_attempts' => 60,
            'decay_minutes' => 1,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Session Security
    |--------------------------------------------------------------------------
    |
    | Enhanced session security settings.
    |
    */
    'session' => [
        'encrypt' => env('SESSION_ENCRYPT', true),
        'secure' => env('SESSION_SECURE_COOKIE', true),
        'http_only' => env('SESSION_HTTP_ONLY', true),
        'same_site' => env('SESSION_SAME_SITE', 'strict'),
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Security
    |--------------------------------------------------------------------------
    |
    | Configure allowed file types and maximum sizes.
    |
    */
    'uploads' => [
        'max_size' => 10240, // 10MB in KB
        'allowed_extensions' => [
            'xlsx', 'xls', 'csv', // Excel files
            'pdf', // Documents
            'jpg', 'jpeg', 'png', 'gif', // Images
        ],
        'allowed_mimes' => [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-excel',
            'text/csv',
            'application/pdf',
            'image/jpeg',
            'image/png',
            'image/gif',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Logging
    |--------------------------------------------------------------------------
    |
    | Configure what security events should be logged.
    |
    */
    'logging' => [
        'log_failed_logins' => true,
        'log_successful_logins' => true,
        'log_permission_denials' => true,
        'log_data_exports' => true,
        'log_file_uploads' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Policy
    |--------------------------------------------------------------------------
    |
    | Define password requirements for user accounts.
    |
    */
    'password' => [
        'min_length' => 8,
        'require_uppercase' => true,
        'require_lowercase' => true,
        'require_numbers' => true,
        'require_special_chars' => false,
        'expires_days' => 90, // Password expiration (0 = never)
    ],

    /*
    |--------------------------------------------------------------------------
    | IP Whitelist/Blacklist
    |--------------------------------------------------------------------------
    |
    | Configure IP-based access control (optional).
    |
    */
    'ip_filtering' => [
        'enabled' => env('IP_FILTERING_ENABLED', false),
        'whitelist' => explode(',', env('IP_WHITELIST', '')),
        'blacklist' => explode(',', env('IP_BLACKLIST', '')),
    ],

    /*
    |--------------------------------------------------------------------------
    | Two-Factor Authentication
    |--------------------------------------------------------------------------
    |
    | Enable/disable 2FA features.
    |
    */
    '2fa' => [
        'enabled' => env('TWO_FACTOR_ENABLED', false),
        'required_for_admin' => true,
    ],

];
