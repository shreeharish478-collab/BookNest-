<?php
require 'config/database.php';

$stmt = $pdo->query('SELECT id, title, pdf_file FROM books');
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);

print_r($books);
