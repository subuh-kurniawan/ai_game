<?php
// gemini_fetch.php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ambil request JSON dari frontend
$input = json_decode(file_get_contents('php://input'), true);
$department = $input['department'] ?? '';
$simulationType = $input['simulationType'] ?? '';

if (!$department || !$simulationType) {
    echo json_encode(['error' => 'Parameter department atau simulationType kosong']);
    exit;
}

// ====== Konfigurasi API Gemini Flash ======
$apiKey = "APIKEY"; // ganti dengan API key valid
$endpoint = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-preview-05-20:generateContent";

// Schema JSON untuk paksa respons Gemini
$responseSchema = [
    "type"=>"OBJECT",
    "properties"=>[
        "levels"=>[
            "type"=>"ARRAY",
            "items"=>[
                "type"=>"OBJECT",
                "properties"=>[
                    "problem"=>["type"=>"STRING"],
                    "correct"=>["type"=>"STRING"],
                    "feedback"=>["type"=>"STRING"]
                ],
                "required"=>["problem","correct","feedback"]
            ]
        ],
        "tools"=>[
            "type"=>"ARRAY",
            "items"=>[
                "type"=>"OBJECT",
                "properties"=>[
                    "id"=>["type"=>"STRING"],
                    "label"=>["type"=>"STRING"]
                ],
                "required"=>["id","label"]
            ]
        ]
    ],
    "required"=>["levels","tools"]
];

// System instruction
$systemInstruction = "Anda adalah Ahli/Pakar Diagnostik SMK. Buat paket simulasi diagnostik 2 level untuk Jurusan: '$department', Kasus: '$simulationType'. Hasil HARUS JSON murni sesuai schema berikut. Jangan ada teks tambahan.";

// Payload request
$payload = [
    "contents"=>[["parts"=>[["text"=>"Buat paket simulasi diagnostik sesuai instruksi: Jurusan '$department', Kasus '$simulationType'. Keluaran JSON sesuai schema."]]]],
    "systemInstruction"=>["parts"=>[["text"=>$systemInstruction]]],
    "responseMimeType"=>"application/json",
    "responseSchema"=>$responseSchema,
    "temperature"=>0.3,
    "candidate_count"=>1,
    "max_output_tokens"=>1200
];

// Retry 3x jika respons kosong
$maxRetries = 3;
$success = false;
$responseText = '';

for($i=0;$i<$maxRetries;$i++){
    $ch = curl_init($endpoint . "?key=" . $apiKey);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    $res = curl_exec($ch);
    if(curl_errno($ch)){
        $err = curl_error($ch);
        curl_close($ch);
        continue;
    }
    curl_close($ch);
    
    // Ambil text dari candidates
    $jsonRes = json_decode($res, true);
    if(isset($jsonRes['candidates'][0]['content'][0]['text']) && trim($jsonRes['candidates'][0]['content'][0]['text'])){
        $responseText = $jsonRes['candidates'][0]['content'][0]['text'];
        $success = true;
        break;
    }
    // Delay 1 detik sebelum retry
    sleep(1);
}

if(!$success){
    echo json_encode(['error'=>'Respon Gemini kosong setelah 3 percobaan']);
    exit;
}

// Ambil JSON murni dari text (regex untuk amankan parsing)
if(preg_match('/\{[\s\S]*\}/',$responseText,$matches)){
    $jsonText = $matches[0];
    $parsed = json_decode($jsonText,true);
    if($parsed && isset($parsed['levels']) && isset($parsed['tools'])){
        echo json_encode($parsed);
        exit;
    } else {
        echo json_encode(['error'=>'Respons Gemini tidak sesuai schema']);
        exit;
    }
} else {
    echo json_encode(['error'=>'Tidak ada JSON valid dalam respons Gemini']);
    exit;
}
