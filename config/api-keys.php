<?php

// Google Gemini API Endpoint (Gemini 1.5 Flash)
define('CHATBOT_ENDPOINT', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent');

// Load environment variables
require_once __DIR__ . '/env_loader.php';
loadEnv(__DIR__ . '/../.env');

// Store Gemini API Key
$apiKey = getenv('GEMINI_API_KEY');
if (!$apiKey) {
    error_log("GEMINI_API_KEY not found in .env file");
}
define('GEMINI_API_KEY', $apiKey);
?>