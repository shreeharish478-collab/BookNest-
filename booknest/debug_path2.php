<?php
require 'config/database.php';

// Fetch a real book to test with
$stmt = $pdo->query('SELECT id, title, pdf_file FROM books WHERE pdf_file IS NOT NULL AND pdf_file != "" LIMIT 1');
$book = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$book) {
    die("No books with PDFs found.");
}

$pdf_file = $book['pdf_file'];

echo "Original DB pdf_file: " . $pdf_file . "\n\n";

// Sanitize backslashes to forward slashes (fixes Windows path issues)
$pdf_file = str_replace('\\', '/', $pdf_file);

echo "After backslash removal: " . $pdf_file . "\n\n";

// Support both database formats: only filename or full relative path
if (strpos($pdf_file, 'uploads/') !== false) {
    // Format: 'uploads/books/book.pdf'
    $pdf_url = '/booknest/' . ltrim($pdf_file, '/');
} else {
    // Format: 'book.pdf'
    $pdf_url = '/booknest/uploads/books/' . ltrim($pdf_file, '/');
}

echo "Generated pdf_url for Browser: " . $pdf_url . "\n\n";

// Generate server file path to verify the file exists before loading
$server_path = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . $pdf_url;
$server_path2 = $_SERVER['DOCUMENT_ROOT'] . $pdf_url;
$server_path3 = realpath(__DIR__ . '/..') . $pdf_url;


echo "DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "Attempted server_path 1: " . $server_path . " (Exists? " . (file_exists($server_path) ? 'Yes' : 'No') . ")\n";
echo "Attempted server_path 2: " . $server_path2 . " (Exists? " . (file_exists($server_path2) ? 'Yes' : 'No') . ")\n";
echo "Attempted server_path 3: " . $server_path3 . " (Exists? " . (file_exists($server_path3) ? 'Yes' : 'No') . ")\n";

// What is the actual path to the uploads folder?
$actual_uploads = realpath(__DIR__ . '/uploads/books');
echo "\nActual absolute path to uploads/books: " . $actual_uploads . "\n";
