<?php
// books/ajax_search.php
require_once '../config/database.php';

header('Content-Type: application/json');

$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (strlen($query) >= 2) {
    // Only return top 5 matches
    $stmt = $pdo->prepare("SELECT id, title, author FROM books WHERE title LIKE ? OR author LIKE ? LIMIT 5");
    $stmt->execute(["%$query%", "%$query%"]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($results);
} else {
    echo json_encode([]);
}
?>
