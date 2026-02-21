<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$apiKey = 'API_KEY_HERE';

$input = json_decode(file_get_contents("php://input"), true);

if (!isset($input['messages'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing messages']);
    exit;
}

$promptTexts = array_map(fn($msg) => $msg['content']['text'] ?? '', $input['messages']);
$promptCombined = implode("\n", $promptTexts);

$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=$apiKey";

$post_fields = json_encode([
    "prompt" => $promptCombined,
    "temperature" => 0.7,
    "candidateCount" => 1
]);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);

$response = curl_exec($ch);
file_put_contents("debug_gemini_response.txt", $response);

if (curl_errno($ch)) {
    http_response_code(500);
    echo json_encode(['error' => curl_error($ch)]);
} else {
    echo $response;
}
curl_close($ch);
