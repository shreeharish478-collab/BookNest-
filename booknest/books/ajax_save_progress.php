<?php
// books/ajax_save_progress.php
require_once '../config/database.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    die("Unauthorized");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $book_id = isset($_POST['book_id']) ? (int)$_POST['book_id'] : 0;
    $last_page = isset($_POST['last_page']) ? (int)$_POST['last_page'] : 1;

    if ($book_id > 0 && $last_page > 0) {
        // Upsert progress
        $stmt = $pdo->prepare("INSERT INTO reading_progress (user_id, book_id, last_page) 
                               VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE last_page = ?");
        if ($stmt->execute([$user_id, $book_id, $last_page, $last_page])) {
            echo "Success";
        } else {
            http_response_code(500);
            echo "Failed";
        }
    }
}
?>
