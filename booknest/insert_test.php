<?php
require 'config/database.php';

$stmt = $pdo->prepare("INSERT INTO books (title, author, description, category, cover_image, pdf_file) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->execute(['Test Book', 'Test Author', 'Test Desc', 'Fiction', '', 'dummy.pdf']);

$insertId = $pdo->lastInsertId();
echo "Inserted book ID: " . $insertId . "\n";

// Create a dummy PDF file so `file_exists` passes
file_put_contents('uploads/books/dummy.pdf', '%PDF-1.4 dummy content');
echo "Created dummy.pdf in uploads/books/\n";
