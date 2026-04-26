<?php
/**
 * Eurobillr - Enterprise SaaS Invoicing Platform
 * Main Configuration File
 * 
 * @package Eurobillr
 * @version 1.0.0
 */

// Prevent direct access
defined('EUROBILLR') or define('EUROBILLR', true);

// Application root
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('CORE_PATH', BASE_PATH . '/core');
define('PUBLIC_PATH', BASE_PATH . '/public');
define('STORAGE_PATH', BASE_PATH . '/storage');
define('DATABASE_PATH', BASE_PATH . '/database');

// Version
define('APP_VERSION', '1.0.0');
define('APP_NAME', 'Eurobillr');

// Environment
define('ENVIRONMENT', getenv('APP_ENV') ?: 'production');
define('DEBUG_MODE', ENVIRONMENT !== 'production');

// Database Configuration
return [
    'database' => [
        'driver' => getenv('DB_DRIVER') ?: 'mysql',
        'host' => getenv('DB_HOST') ?: 'localhost',
        'port' => getenv('DB_PORT') ?: 3306,
        'database' => getenv('DB_DATABASE') ?: 'eurobillr',
        'username' => getenv('DB_USERNAME') ?: 'root',
        'password' => getenv('DB_PASSWORD') ?: '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => 'eb_',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    ],

    // Redis Cache (optional)
    'redis' => [
        'host' => getenv('REDIS_HOST') ?: '127.0.0.1',
        'port' => (int)(getenv('REDIS_PORT') ?: 6379),
        'password' => getenv('REDIS_PASSWORD') ?: null,
        'database' => (int)(getenv('REDIS_DB') ?: 0),
    ],

    // Session Configuration
    'session' => [
        'driver' => 'database', // database, file, redis
        'lifetime' => 120,
        'expire_on_close' => false,
        'encrypt' => true,
        'cookie' => 'eb_session',
        'path' => '/',
        'domain' => getenv('SESSION_DOMAIN') ?: null,
        'secure' => ENVIRONMENT === 'production',
        'http_only' => true,
        'same_site' => 'lax',
    ],

    // Security
    'security' => [
        'csrf_token_name' => '_csrf_token',
        'csrf_expire' => 7200,
        'encryption_key' => getenv('APP_KEY') ?: '',
        'cipher' => 'AES-256-CBC',
        'hash_cost' => 12,
        'max_login_attempts' => 5,
        'lockout_time' => 300, // 5 minutes
    ],

    // Mail Configuration (Symfony Mailer)
    'mail' => [
        'driver' => getenv('MAIL_DRIVER') ?: 'smtp',
        'host' => getenv('MAIL_HOST') ?: 'smtp.mailtrap.io',
        'port' => (int)(getenv('MAIL_PORT') ?: 587),
        'username' => getenv('MAIL_USERNAME'),
        'password' => getenv('MAIL_PASSWORD'),
        'encryption' => getenv('MAIL_ENCRYPTION') ?: 'tls',
        'from_address' => getenv('MAIL_FROM_ADDRESS') ?: 'noreply@eurobillr.com',
        'from_name' => getenv('MAIL_FROM_NAME') ?: APP_NAME,
    ],

    // File Storage
    'storage' => [
        'driver' => getenv('FILESYSTEM_DRIVER') ?: 'local',
        'local_root' => PUBLIC_PATH . '/uploads',
        's3_key' => getenv('AWS_ACCESS_KEY_ID'),
        's3_secret' => getenv('AWS_SECRET_ACCESS_KEY'),
        's3_region' => getenv('AWS_DEFAULT_REGION') ?: 'eu-west-1',
        's3_bucket' => getenv('AWS_BUCKET'),
        's3_url' => getenv('AWS_URL'),
        'max_upload_size' => 10 * 1024 * 1024, // 10MB
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'pdf', 'xml', 'csv', 'xlsx'],
    ],

    // Peppol Configuration
    'peppol' => [
        'enabled' => filter_var(getenv('PEPPOL_ENABLED'), FILTER_VALIDATE_BOOLEAN),
        'access_point_id' => getenv('PEPPOL_ACCESS_POINT_ID'),
        'access_point_secret' => getenv('PEPPOL_ACCESS_POINT_SECRET'),
        'api_url' => getenv('PEPPOL_API_URL') ?: 'https://api.peppol.eu',
        'environment' => getenv('PEPPOL_ENV') ?: 'production', // production, test
    ],

    // Payment Gateways
    'payments' => [
        'stripe' => [
            'enabled' => filter_var(getenv('STRIPE_ENABLED'), FILTER_VALIDATE_BOOLEAN),
            'public_key' => getenv('STRIPE_PUBLIC_KEY'),
            'secret_key' => getenv('STRIPE_SECRET_KEY'),
            'webhook_secret' => getenv('STRIPE_WEBHOOK_SECRET'),
        ],
        'mollie' => [
            'enabled' => filter_var(getenv('MOLLIE_ENABLED'), FILTER_VALIDATE_BOOLEAN),
            'api_key' => getenv('MOLLIE_API_KEY'),
            'webhook_url' => getenv('MOLLIE_WEBHOOK_URL'),
        ],
        'paypal' => [
            'enabled' => filter_var(getenv('PAYPAL_ENABLED'), FILTER_VALIDATE_BOOLEAN),
            'client_id' => getenv('PAYPAL_CLIENT_ID'),
            'secret' => getenv('PAYPAL_SECRET'),
            'mode' => getenv('PAYPAL_MODE') ?: 'live',
        ],
    ],

    // OCR (Amazon Textract)
    'ocr' => [
        'driver' => getenv('OCR_DRIVER') ?: 'textract',
        'aws_key' => getenv('AWS_ACCESS_KEY_ID'),
        'aws_secret' => getenv('AWS_SECRET_ACCESS_KEY'),
        'aws_region' => getenv('AWS_DEFAULT_REGION') ?: 'eu-west-1',
    ],

    // Exchange Rates API
    'exchange_rates' => [
        'driver' => 'exchangerate', // exchangerate, fixer, openexchangerates
        'api_key' => getenv('EXCHANGE_RATES_API_KEY'),
        'base_currency' => 'EUR',
        'cache_hours' => 24,
    ],

    // VAT Lookup APIs
    'vat_lookup' => [
        'vies_enabled' => true,
        'cbe_api_key' => getenv('CBE_API_KEY'), // Belgium CBE API
    ],

    // Supported Languages
    'languages' => [
        'en' => ['name' => 'English', 'flag' => '🇬🇧', 'locale' => 'en_GB'],
        'nl' => ['name' => 'Nederlands', 'flag' => '🇳🇱', 'locale' => 'nl_NL'],
        'fr' => ['name' => 'Français', 'flag' => '🇫🇷', 'locale' => 'fr_FR'],
        'de' => ['name' => 'Deutsch', 'flag' => '🇩🇪', 'locale' => 'de_DE'],
        'es' => ['name' => 'Español', 'flag' => '🇪🇸', 'locale' => 'es_ES'],
    ],
    'default_language' => 'en',

    // Supported Currencies
    'currencies' => [
        'EUR' => ['symbol' => '€', 'name' => 'Euro', 'precision' => 2],
        'USD' => ['symbol' => '$', 'name' => 'US Dollar', 'precision' => 2],
        'GBP' => ['symbol' => '£', 'name' => 'British Pound', 'precision' => 2],
        'CHF' => ['symbol' => 'CHF', 'name' => 'Swiss Franc', 'precision' => 2],
        'SEK' => ['symbol' => 'kr', 'name' => 'Swedish Krona', 'precision' => 2],
        'DKK' => ['symbol' => 'kr', 'name' => 'Danish Krone', 'precision' => 2],
        'NOK' => ['symbol' => 'kr', 'name' => 'Norwegian Krone', 'precision' => 2],
        'PLN' => ['symbol' => 'zł', 'name' => 'Polish Zloty', 'precision' => 2],
        'CZK' => ['symbol' => 'Kč', 'name' => 'Czech Koruna', 'precision' => 2],
        'HUF' => ['symbol' => 'Ft', 'name' => 'Hungarian Forint', 'precision' => 2],
    ],
    'default_currency' => 'EUR',

    // Country to Currency mapping
    'country_currency' => [
        'BE' => 'EUR', 'NL' => 'EUR', 'FR' => 'EUR', 'DE' => 'EUR',
        'ES' => 'EUR', 'IT' => 'EUR', 'PT' => 'EUR', 'AT' => 'EUR',
        'GB' => 'GBP', 'CH' => 'CHF', 'SE' => 'SEK', 'DK' => 'DKK',
        'NO' => 'NOK', 'PL' => 'PLN', 'CZ' => 'CZK', 'HU' => 'HUF',
        'US' => 'USD',
    ],

    // Date formats per locale
    'date_formats' => [
        'en_GB' => 'd/m/Y',
        'en_US' => 'm/d/Y',
        'nl_NL' => 'd-m-Y',
        'fr_FR' => 'd/m/Y',
        'de_DE' => 'd.m.Y',
        'es_ES' => 'd/m/Y',
    ],
    'datetime_format' => 'Y-m-d H:i:s',

    // Pagination
    'pagination' => [
        'per_page' => 20,
        'max_pages' => 100,
    ],

    // Queue Configuration
    'queue' => [
        'driver' => 'redis', // redis, database, sync
        'default_queue' => 'default',
        'retry_after' => 90,
    ],

    // Logging
    'logging' => [
        'driver' => 'daily',
        'level' => DEBUG_MODE ? 'debug' : 'error',
        'days' => 30,
        'path' => STORAGE_PATH . '/logs',
    ],

    // Rate Limiting
    'rate_limiting' => [
        'enabled' => true,
        'requests_per_minute' => 60,
        'login_attempts' => 5,
        'api_requests' => 100,
    ],

    // Feature Flags
    'features' => [
        'peppol_enabled' => true,
        'ocr_enabled' => true,
        'multi_currency' => true,
        'recurring_invoices' => true,
        'payment_reminders' => true,
        'team_collaboration' => true,
        'api_access' => true,
        'webhooks' => true,
    ],

    // URLs
    'urls' => [
        'app' => getenv('APP_URL') ?: 'https://eurobillr.com',
        'api' => getenv('API_URL') ?: 'https://api.eurobillr.com',
        'cdn' => getenv('CDN_URL') ?: '',
    ],

    // Timezone
    'timezone' => getenv('APP_TIMEZONE') ?: 'Europe/Brussels',
];
