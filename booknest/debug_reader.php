<?php
require 'config/database.php';

$book_id = 9;
$stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
$stmt->execute([$book_id]);
$book = $stmt->fetch();

$pdf_file = $book['pdf_file'];

// Sanitize backslashes to forward slashes
$pdf_file = str_replace('\\', '/', $pdf_file);

if (strpos($pdf_file, 'uploads/') !== false) {
    // Format: 'uploads/books/book.pdf'
    $pdf_url = '/booknest/' . ltrim($pdf_file, '/');
} else {
    // Format: 'book.pdf'
    $pdf_url = '/booknest/uploads/books/' . ltrim($pdf_file, '/');
}

$server_path = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . $pdf_url;

echo "URL: " . $pdf_url . "\n";
echo "Server Path: " . $server_path . "\n";
echo "DOCUMENT_ROOT: " . rtrim($_SERVER['DOCUMENT_ROOT'], '/') . "\n";
echo "Exists? " . (file_exists($server_path) ? 'Yes' : 'No') . "\n";
