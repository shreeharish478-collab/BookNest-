<?php
$file = 'uploads/books/1773339116_69b301ec398dc.pdf';
if (file_exists($file)) {
    echo "File exists.\n";
    $bytes = file_get_contents($file, false, null, 0, 5);
    echo "First 5 bytes: " . bin2hex($bytes) . " (" . $bytes . ")\n";
    if ($bytes === '%PDF-') {
        echo "Valid PDF header.\n";
    } else {
        echo "INVALID PDF HEADER!\n";
    }
} else {
    echo "File does not exist at $file\n";
}
