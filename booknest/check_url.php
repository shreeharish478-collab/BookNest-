<?php
$pdf_file = '1773339116_69b301ec398dc.pdf';
$is_relative = (strpos($pdf_file, 'uploads/') !== false);

$base_dir = realpath(__DIR__ . '/books/..');

if ($is_relative) {
    $pdf_url = '/booknest/' . ltrim($pdf_file, '/');
    $server_path = $base_dir . '/' . ltrim($pdf_file, '/');
} else {
    $pdf_url = '/booknest/uploads/books/' . ltrim($pdf_file, '/');
    $server_path = $base_dir . '/uploads/books/' . ltrim($pdf_file, '/');
}

echo "URL: " . $pdf_url . "\n";
echo "Server Path: " . $server_path . "\n";
echo "Valid file? " . (file_exists($server_path) ? 'Yes' : 'No') . "\n";
