<?php
// File: update_counter.php
header('Content-Type: application/json');

// Path file JSON
$jsonFile = 'counter.json';

// Ambil data saat ini
if (file_exists($jsonFile)) {
    $data = json_decode(file_get_contents($jsonFile), true);
} else {
    $data = ['count' => 0];
}

// Tambahkan 1
$data['count'] = isset($data['count']) ? $data['count'] + 1 : 1;

// Simpan kembali ke file JSON
file_put_contents($jsonFile, json_encode($data, JSON_PRETTY_PRINT));

// Kembalikan data terbaru sebagai response
echo json_encode($data);
