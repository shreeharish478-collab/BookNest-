<?php
// library/ajax_save_book.php
require_once '../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $book_id = isset($_POST['book_id']) ? (int)$_POST['book_id'] : 0;

    if ($book_id > 0) {
        $stmt = $pdo->prepare("SELECT id FROM library WHERE user_id = ? AND book_id = ?");
        $stmt->execute([$user_id, $book_id]);
        
        if ($stmt->rowCount() > 0) {
            // Exists, so remove
            $del = $pdo->prepare("DELETE FROM library WHERE user_id = ? AND book_id = ?");
            $del->execute([$user_id, $book_id]);
            echo json_encode(['status' => 'removed']);
        } else {
            // Does not exist, add
            $ins = $pdo->prepare("INSERT INTO library (user_id, book_id) VALUES (?, ?)");
            $ins->execute([$user_id, $book_id]);
            echo json_encode(['status' => 'added']);
        }
    } else {
         echo json_encode(['status' => 'error']);
    }
}
?>
