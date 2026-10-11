<?php
// Sample environment configuration file
// Copy this file to backend/config/env.php and update with your settings

return [
    // Database Configuration
    'db' => [
        'host' => 'localhost',
        'user' => 'root',
        'password' => '',
        'name' => 'suvan_ventures',
        'charset' => 'utf8mb4'
    ],

    // Application Settings
    'app' => [
        'name' => 'Suvan Ventures',
        'url' => 'http://localhost/moneyCome',
        'timezone' => 'UTC',
        'debug' => true // Set to false in production
    ],

    // Session Configuration
    'session' => [
        'timeout' => 3600, // 1 hour in seconds
        'name' => 'suvan_ventures_session'
    ],

    // Payment Gateway Configuration
    'payment' => [
        'gateway' => 'paypal', // or 'stripe', 'bank_transfer'
        'sandbox' => true, // Use sandbox for testing
        'api_key' => 'your_api_key_here',
        'api_secret' => 'your_api_secret_here'
    ],

    // Email Configuration (for order confirmations, etc.)
    'email' => [
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'user' => 'your_email@gmail.com',
        'password' => 'your_app_password',
        'from_name' => 'Suvan Ventures',
        'from_address' => 'noreply@suvanventures.com'
    ],

    // Security
    'security' => [
        'password_hash_algorithm' => 'sha256',
        'max_login_attempts' => 5,
        'lockout_duration' => 900 // 15 minutes in seconds
    ],

    // Logging
    'log' => [
        'enabled' => true,
        'path' => dirname(__DIR__) . '/logs/',
        'level' => 'error' // 'debug', 'info', 'warning', 'error'
    ]
];
?>
