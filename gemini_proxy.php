<?php
session_start();
date_default_timezone_set('Asia/Jakarta');
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
include '../admin/fungsi/koneksi.php'; // Pastikan koneksi database tersedia

$sql = mysqli_query($koneksi, "
    SELECT api_key
    FROM api_keys
    WHERE usage_count = (SELECT MIN(usage_count) FROM api_keys)
    ORDER BY RAND()
    LIMIT 1
");

if (!$sql) {
    die("Error fetching API key: " . mysqli_error($koneksi));
}

$dataApiKey = mysqli_fetch_assoc($sql);

if ($dataApiKey) {
    $apiKey = $dataApiKey['api_key'];
} else {
    die("No API keys found in the database.");
}
$models = [];

$sql = "SELECT model_name 
        FROM api_model 
        WHERE is_supported = 1 
        ORDER BY id ASC";

$res = $koneksi->query($sql);

while ($row = $res->fetch_assoc()) {
    $models[] = $row['model_name'];
}

// Fallback jika database kosong atau tidak ada model yang didukung
if (empty($models)) {
    $models[] = "gemini-2.5-flash"
    }

// Pilih model pertama / default
$model = $models[0];

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

// Endpoint Gemini
$url = "https://generativelanguage.googleapis.com/v1beta/models/$model:generateContent?key=$apiKey";

// Payload ke Gemini
$payload = [
    "contents" => [
        [
            "parts" => [
                ["text" => $promptCombined]
            ]
        ]
    ]
];

// Kirim request
$options = [
    'http' => [
        'method'  => 'POST',
        'header'  => "Content-Type: application/json\r\n",
        'content' => json_encode($payload),
    ]
];

$context  = stream_context_create($options);
$response = @file_get_contents($url, false, $context);

// Tangani error koneksi
if ($response === false) {
    $error = error_get_last();
    http_response_code(500);
    echo json_encode(['error' => 'API request failed', 'detail' => $error['message'] ?? 'Unknown error']);
    exit;
}

// Cek HTTP status response
$httpCode = null;
if (isset($http_response_header)) {
    foreach ($http_response_header as $header) {
        if (preg_match('#HTTP/\d+\.\d+\s+(\d+)#', $header, $matches)) {
            $httpCode = intval($matches[1]);
            break;
        }
    }
}

if ($httpCode >= 400) {
    http_response_code($httpCode);
    echo json_encode([
        'error' => "Gemini API returned HTTP $httpCode",
        'response' => $response
    ]);
    exit;
}

// Sukses
echo $response;
