<?php
require 'config/database.php';
$stmt = $pdo->query('SELECT id, title, pdf_file FROM books LIMIT 3');
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "SCRIPT_FILENAME: " . $_SERVER['SCRIPT_FILENAME'] . "\n";
print_r($books);
