<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Ganti dengan API key milikmu
$apiKey = 'API_KEY_HERE'; // --- IGNORE ---

// Ambil input dari frontend
$input = json_decode(file_get_contents("php://input"), true);

if (!isset($input['messages'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing messages']);
    exit;
}

// Ambil prompt sebagai teks
$promptTexts = array_map(function ($msg) {
    return is_array($msg['content']) ? $msg['content']['text'] ?? '' : $msg['content'];
}, $input['messages']);

$promptCombined = implode("\n", $promptTexts);

// Siapkan payload untuk Gemini
$payload = [
    "contents" => [
        [
            "role" => "user",
            "parts" => [
                ["text" => $promptCombined]
            ]
        ]
    ]
];

// Simpan payload ke file (debug/log)
file_put_contents("last_payload.json", json_encode($payload, JSON_PRETTY_PRINT));

// Model fallback list
$models = [
    "gemini-2.0-flash",
    "gemini-2.0-flash-lite",
    "gemini-1.5-flash"
];

$modelIndex = 0;
$maxRetries = 3;
$lastErrorMsg = '';
$response = false;
$httpCode = 0;

while ($modelIndex < count($models)) {
    $model = $models[$modelIndex];
    $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

    $options = [
        'http' => [
            'method'  => 'POST',
            'header'  => "Content-Type: application/json\r\n",
            'content' => json_encode($payload),
            'timeout' => 10
        ]
    ];
    $context = stream_context_create($options);

    $retryCount = 0;

    while ($retryCount < $maxRetries) {
        $response = @file_get_contents($url, false, $context);

        // Ambil HTTP code dari response header
        $httpCode = null;
        if (isset($http_response_header)) {
            foreach ($http_response_header as $header) {
                if (preg_match('#HTTP/\d+\.\d+\s+(\d+)#', $header, $matches)) {
                    $httpCode = intval($matches[1]);
                    break;
                }
            }
        }

        if ($response !== false && $httpCode >= 200 && $httpCode < 300) {
            echo $response;
            exit;
        } else {
            $error = error_get_last();
            $lastErrorMsg = $error['message'] ?? "HTTP $httpCode from model $model";
            $retryCount++;
            usleep(500000); // 0.5 detik delay sebelum retry
        }
    }

    $modelIndex++; // Pindah ke model berikutnya jika gagal
}

// Jika semua model gagal
http_response_code(500);
echo json_encode([
    'error' => 'All fallback models failed',
    'last_model_tried' => $models[$modelIndex - 1] ?? null,
    'last_http_code' => $httpCode,
    'last_error' => $lastErrorMsg
]);
exit;
