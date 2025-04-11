<?php
function convertHtmlToPdf($html) {
    // Use PHP's output buffering
    ob_start();
    
    // Create a temporary file
    $tempfile = tempnam(sys_get_temp_dir(), 'pdf');
    file_put_contents($tempfile, $html);
    
    // Use wkhtmltopdf if available, otherwise fallback to basic HTML
    if (checkWkhtmltopdf()) {
        exec('wkhtmltopdf ' . $tempfile . ' ' . $tempfile . '.pdf');
        $pdf = file_get_contents($tempfile . '.pdf');
        unlink($tempfile . '.pdf');
    } else {
        // Fallback to basic HTML with PDF headers
        $pdf = $html;
    }
    
    unlink($tempfile);
    ob_end_clean();
    
    return $pdf;
}

function checkWkhtmltopdf() {
    exec('wkhtmltopdf --version', $output, $return);
    return $return === 0;
}
