<?php
// Load environment variables
require_once __DIR__ . '/../config/env_loader.php';
loadEnv(__DIR__ . '/../.env');

$apiKey = getenv('GEMINI_API_KEY');
if (!$apiKey) {
    die("Error: GEMINI_API_KEY not found in .env\n");
}

$url = "https://generativelanguage.googleapis.com/v1beta/models?key=" . $apiKey;

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

if ($curl_error) {
    die("Connection Error: $curl_error\n");
}

$data = json_decode($response, true);

if ($http_code !== 200) {
    echo "API Error ($http_code):\n";
    print_r($data);
    exit;
}

echo "Available Models:\n";
if (isset($data['models'])) {
    foreach ($data['models'] as $model) {
        if (in_array('generateContent', $model['supportedGenerationMethods'])) {
            echo "- " . $model['name'] . " (" . $model['displayName'] . ")\n";
        }
    }
} else {
    echo "No models found in response.\n";
    print_r($data);
}
?>