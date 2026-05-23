<?php
// config/payment.php

// Configuration Wave
define('WAVE_API_URL', 'https://api.wave.com/v1/');
define('WAVE_API_KEY', 'votre_api_key_wave');
define('WAVE_WEBHOOK_SECRET', 'votre_webhook_secret');
define('WAVE_BUSINESS_ID', 'votre_business_id');

// Configuration Orange Money
define('ORANGE_API_URL', 'https://api.orange.com/orange-money-webpay/cm/v1/webpayment');
define('ORANGE_TOKEN_URL', 'https://api.orange.com/oauth/v2/token');
define('ORANGE_CLIENT_ID', 'votre_client_id');
define('ORANGE_CLIENT_SECRET', 'votre_client_secret');
define('ORANGE_MERCHANT_ID', 'votre_merchant_id');
define('ORANGE_RETURN_URL', url('payment_success.php'));
define('ORANGE_CANCEL_URL', url('payment_cancel.php'));
define('ORANGE_NOTIFY_URL', url('payment_notify.php'));

// Mode test (true = sandbox, false = production)
define('PAYMENT_TEST_MODE', false);