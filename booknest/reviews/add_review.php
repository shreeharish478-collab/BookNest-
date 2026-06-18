<?php
// reviews/add_review.php
require_once '../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $book_id = isset($_POST['book_id']) ? (int)$_POST['book_id'] : 0;
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
    $review_text = trim($_POST['review_text']);

    if ($book_id > 0 && $rating >= 1 && $rating <= 5) {
        $stmt = $pdo->prepare("INSERT INTO reviews (user_id, book_id, rating, review_text) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $book_id, $rating, $review_text]);
        
        // Redirect back to book details
        header("Location: ../books/book_details.php?id=" . $book_id);
        exit();
    }
}

// Fallback error redirection
header("Location: ../index.php");
exit();
?>
