<?php

require_once '../config/api-keys.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $user_message = trim($_POST['message']);

    // 1. Payload Construction
    $data = [
        "model" => "google/gemma-3-27b-it:free",
        "messages" => [
            [
                "role" => "system",
                "content" => "You are MakanMystery Web Support, a helpful assistant specialized in answering questions about surplus food, local pickup procedures, vendors, and marketplace rules in Malaysia. Keep answers brief and focused on food rescue."
            ],
            [
                "role" => "user",
                "content" => $user_message
            ]
        ],
        "stream" => false,
        "max_tokens" => 400,
        "temperature" => 0.7
    ];

    // 2. Initialize cURL request
    $ch = curl_init(CHATBOT_ENDPOINT);

    // 3. Set cURL options
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . CHATBOT_API_KEY
    ]);

    // 4. EXECUTE THE REQUEST & COLLECT ERRORS
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    $curl_errno = curl_errno($ch);
    curl_close($ch);

    // ----------------------------------------------------
    // CHECK: Network/API Error Check
    // ----------------------------------------------------

    if ($curl_error || $curl_errno !== 0) {
        // Handles failure to reach the server (DNS/SSL/Network)
        error_log("CURL NETWORK FAILURE: Code {$curl_errno} - Message: {$curl_error}");
        http_response_code(500);

        $reply = "CURL FAILED. Code {$curl_errno}: {$curl_error}. Check API Key/Network.";
        echo json_encode(['reply' => $reply]);
        exit;
    }

    if ($http_code !== 200) {
        // Handles successful connection but API rejection (e.g., Status 401/400)

        $decoded_response = json_decode($response, true);
        $error_details = $decoded_response['error']['message'] ?? "Unknown API Error.";

        http_response_code(500);
        $reply = "API Error (Code {$http_code}). Check Key/Restrictions. Details: {$error_details}";
        echo json_encode(['reply' => $reply]);
        exit;
    }

    // 5. Process and return response
    $decoded_response = json_decode($response, true);

    $reply_text = $decoded_response['choices'][0]['message']['content'] ?? "Sorry, I couldn't find a response.";

    // 6. Return answer as JSON
    echo json_encode(['reply' => $reply_text]);

} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request method or missing message.']);
}