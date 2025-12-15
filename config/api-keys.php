<?php

// FIX 1: Change Endpoint to OpenRouter's Unified endpoint
define('CHATBOT_ENDPOINT', 'https://openrouter.ai/api/v1/chat/completions');

// Load environment variables
require_once __DIR__ . '/env_loader.php';
loadEnv(__DIR__ . '/../.env');

// FIX 2: Store your OpenRouter API Key
// This is the key you generated on the openrouter.ai website.
$apiKey = getenv('CHATBOT_API_KEY');
if (!$apiKey) {
    // Optional: Log error or handle missing key, but for now we just define it as false or empty
    error_log("CHATBOT_API_KEY not found in .env file");
}
define('CHATBOT_API_KEY', $apiKey);
?>