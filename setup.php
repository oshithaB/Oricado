<?php
// Download mPDF
$mpdfUrl = 'https://github.com/mpdf/mpdf/releases/download/v8.1.4/mpdf.zip';
$mpdfZip = __DIR__ . '/includes/mpdf.zip';
$mpdfDir = __DIR__ . '/includes/mpdf';

// Create directories if they don't exist
if (!file_exists($mpdfDir)) {
    mkdir($mpdfDir, 0777, true);
}

// Download and extract mPDF
file_put_contents($mpdfZip, file_get_contents($mpdfUrl));
$zip = new ZipArchive;
if ($zip->open($mpdfZip) === TRUE) {
    $zip->extractTo($mpdfDir);
    $zip->close();
    unlink($mpdfZip);
    echo "mPDF installed successfully\n";
} else {
    echo "Failed to install mPDF\n";
}
