<?php
header('Content-Type: application/json');

// ====== API Key Gemini Flash ======
$apiKey = "APIKEY"; // ganti dengan API Key Anda

// ====== Prompt untuk AI ======
$prompt = <<<EOT
Buat alur transaksi pembukuan untuk 1 perusahaan selama 1 minggu untuk siswa SMK Akuntansi.
Sertakan untuk setiap transaksi:
// story (narasi singkat, mudah dipahami siswa)
// transaction (deskripsi transaksi)
// correct_debit
// correct_credit
// options (4 akun termasuk yang benar)
Buat 5 transaksi berurutan.
Kembalikan dalam format JSON seperti:
{
  "transactions": [
    {
      "story": "...",
      "transaction": "...",
      "correct_debit": "...",
      "correct_credit": "...",
      "options": ["...","...","...","..."]
    }
  ],
  "accounts": ["Kas","Modal","Perlengkapan","Pendapatan","Beban Gaji","Piutang Usaha"]
}
EOT;

// ====== Request ke Gemini Flash ======
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://generativelanguage.googleapis.com/v1beta2/models/gemini-2.5-flash:generateContent");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "x-goog-api-key: $apiKey"
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    "contents" => [
        ["parts" => [["text" => $prompt]]]
    ]
]));

$response = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);

// ====== Jika gagal request ======
if (!$response) {
    echo json_encode(["error" => "❌ Gagal memuat transaksi: $err"]);
    exit;
}

// ====== Ambil output dari response ======
$result = json_decode($response, true);
$text = $result['candidates'][0]['output'] ?? null;

if (!$text) {
    echo json_encode(["error" => "❌ Tidak ada output dari API"]);
    exit;
}

// ====== Pastikan JSON valid ======
$json = json_decode($text, true);

// Jika JSON dari AI tidak valid, kembalikan error
if (!$json || !isset($json['transactions']) || !isset($json['accounts'])) {
    echo json_encode(["error" => "❌ Response AI tidak valid"]);
    exit;
}

// ====== Return JSON ke frontend ======
echo json_encode($json);
