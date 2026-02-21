<?php
include "../admin/fungsi/koneksi.php";

function readDocFile($filename) {
    if (!file_exists($filename)) return "File tidak ditemukan.";
    $fileHandle = fopen($filename, "r");
    $line = fread($fileHandle, filesize($filename));
    fclose($fileHandle);
    $lines = explode(chr(0x0D), $line);
    $outtext = "";
    foreach ($lines as $thisline) {
        $pos = strpos($thisline, chr(0x00));
        if (($pos === false) && strlen($thisline) > 0) {
            $outtext .= $thisline . "\n";
        }
    }
    return preg_replace("/[^a-zA-Z0-9\s\,\.\-\n\@\(\)\{\}\:\*\#\[\]\;]/", "", $outtext);
}

if (!isset($_GET['file'])) {
    http_response_code(400);
    echo "Parameter file tidak diberikan.";
    exit;
}

$filename = basename($_GET['file']);
$templatePath = __DIR__ . "/" . $filename;

if (!file_exists($templatePath)) {
    http_response_code(404);
    echo "File tidak ditemukan.";
    exit;
}

echo readDocFile($templatePath);
