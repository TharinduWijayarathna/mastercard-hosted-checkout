<?php
/**
 * Mastercard Gateway Configuration
 * 
 * This file loads configuration from .env file or uses default values
 */

// Load environment variables from .env file if it exists
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Parse key=value pairs
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            if (!array_key_exists($key, $_ENV)) {
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }
    }
}

// Helper function to get env variables with default values
if (!function_exists('env')) {
    function env($key, $default = null) {
        $value = getenv($key);
        if ($value === false) {
            return $default;
        }
        return $value;
    }
}

return [
    // Gateway Environment
    'environment' => env('GATEWAY_ENVIRONMENT', 'test'),
    
    // Merchant Credentials
    'merchant_id' => env('MERCHANT_ID', 'TEST_MERCHANT'),
    'api_password' => env('API_PASSWORD', 'test_password'),
    
    // Gateway URLs
    'gateway_url' => env('GATEWAY_ENVIRONMENT', 'test') === 'test' 
        ? env('GATEWAY_URL_TEST', 'https://test-bankofceylon.mtf.gateway.mastercard.com')
        : env('GATEWAY_URL_PROD', 'https://bankofceylon.gateway.mastercard.com'),
    
    // API Version
    'api_version' => env('API_VERSION', '100'),
    
    // Default Settings
    'currency' => env('CURRENCY', 'LKR'),
    'merchant_name' => env('MERCHANT_NAME', 'My Store'),
    
    // Webhook Settings
    'webhook_secret' => env('WEBHOOK_SECRET', ''),
    
    // Session Settings
    'session_timeout' => 1800, // 30 minutes in seconds
    
    // Logging
    'enable_logging' => env('GATEWAY_ENVIRONMENT', 'test') === 'test',
    'log_file' => __DIR__ . '/logs/gateway.log',
];
