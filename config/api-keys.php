<?php

// OpenRouter's Unified endpoint
define('CHATBOT_ENDPOINT', 'https://openrouter.ai/api/v1/chat/completions');

// Load environment variables
require_once __DIR__ . '/env_loader.php';
loadEnv(__DIR__ . '/../.env');

// Store OpenRouter API Key
$apiKey = getenv('CHATBOT_API_KEY');
if (!$apiKey) {

    error_log("CHATBOT_API_KEY not found in .env file");
}
define('CHATBOT_API_KEY', $apiKey);
?>