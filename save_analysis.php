<?php
include "../admin/fungsi/koneksi.php";
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $analysis_text = isset($_POST['analysis_text']) ? urldecode($_POST['analysis_text']) : '';
    $final_score = isset($_POST['final_score']) ? $_POST['final_score'] : '';
    $nama_guru = isset($_POST['nama_guru']) ? $_POST['nama_guru'] : '';

    // Validasi sederhana
    if (empty($analysis_text) || empty($nama_guru)) {
        echo "Data tidak lengkap!";
        exit;
    }

    // UPSERT: Insert baru atau update jika nama_guru sudah ada
    $stmt = $koneksi->prepare("
        INSERT INTO analisis_hasil_total (nama_guru, final_score, analisis_text, created_at)
        VALUES (?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE
            final_score = VALUES(final_score),
            analisis_text = VALUES(analisis_text),
            created_at = NOW()
    ");

    if ($stmt === false) {
        echo "Gagal mempersiapkan statement: " . $koneksi->error;
        exit;
    }

    $stmt->bind_param("sss", $nama_guru, $final_score, $analysis_text);

    if ($stmt->execute()) {
        echo "Berhasil disimpan/diupdate";
    } else {
        echo "Gagal menyimpan/diupdate: " . $stmt->error;
    }

    $stmt->close();
    $koneksi->close();

} else {
    echo "Request tidak valid!";
}
?>
