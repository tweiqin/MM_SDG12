<?php
// pages/test_models.php
header('Content-Type: text/plain');

// Adjust path to config based on location
if (file_exists(__DIR__ . '/../config/api-keys.php')) {
    require_once __DIR__ . '/../config/api-keys.php';
} else {
    die("Error: Could not find config/api-keys.php");
}

if (!defined('GEMINI_API_KEY') || empty(GEMINI_API_KEY)) {
    die("Error: GEMINI_API_KEY is not set in config.");
}

echo "Checking models for API Key: " . substr(GEMINI_API_KEY, 0, 5) . "...\n";
echo "Endpoint Base: https://generativelanguage.googleapis.com/v1beta/models\n\n";

$url = "https://generativelanguage.googleapis.com/v1beta/models?key=" . GEMINI_API_KEY;

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
// Verify SSL might be an issue on local setups, optionally disable if needed but insecure
// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

if ($curl_error) {
    echo "Connection Failed: $curl_error\n";
    exit;
}

if ($http_code !== 200) {
    echo "API Returned Error Code: $http_code\n";
    echo "Response:\n$response\n";
    exit;
}

$data = json_decode($response, true);

echo "--- AVAILABLE MODELS ---\n";
if (isset($data['models'])) {
    foreach ($data['models'] as $model) {
        $name = $model['name']; // e.g., models/gemini-1.5-flash
        $methods = isset($model['supportedGenerationMethods']) ? implode(', ', $model['supportedGenerationMethods']) : 'None';

        // Filter for generateContent support
        if (strpos($methods, 'generateContent') !== false) {
            echo "Name: $name\n";
            echo "   - Methods: $methods\n\n";
        }
    }
} else {
    echo "No models found in response.\n";
    print_r($data);
}
?>