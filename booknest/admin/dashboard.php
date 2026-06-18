<?php
// admin/dashboard.php
require_once '../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Simple Admin Check (User ID 1 is admin)
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != 1) {
    die("<div style='text-align:center; padding:50px;'><h3>Access Denied. Admins only.</h3><a href='../index.php'>Go Home</a></div>");
}

// Get stats
$stats = [
    'users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'books' => $pdo->query("SELECT COUNT(*) FROM books")->fetchColumn(),
    'reviews' => $pdo->query("SELECT COUNT(*) FROM reviews")->fetchColumn(),
];

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="container mt-5 mb-5 fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-2">
        <h2 class="fw-bold"><i class="fa-solid fa-gauge text-primary me-2"></i> Admin Dashboard</h2>
        <div>
            <a href="upload_book.php" class="btn btn-primary rounded-pill"><i class="fa-solid fa-cloud-arrow-up"></i> Upload Book</a>
            <a href="manage_books.php" class="btn btn-outline-secondary rounded-pill ms-2"><i class="fa-solid fa-list"></i> Manage Books</a>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card bg-primary text-white border-0 shadow-sm text-center py-4 custom-hover">
                <i class="fa-solid fa-users fa-3x mb-3 opacity-75"></i>
                <h3 class="display-6 fw-bold"><?php echo $stats['users']; ?></h3>
                <p class="mb-0 fs-5">Total Users</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white border-0 shadow-sm text-center py-4 custom-hover">
                <i class="fa-solid fa-book fa-3x mb-3 opacity-75"></i>
                <h3 class="display-6 fw-bold"><?php echo $stats['books']; ?></h3>
                <p class="mb-0 fs-5">Total Books</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning text-dark border-0 shadow-sm text-center py-4 custom-hover">
                <i class="fa-solid fa-comments fa-3x mb-3 opacity-75"></i>
                <h3 class="display-6 fw-bold"><?php echo $stats['reviews']; ?></h3>
                <p class="mb-0 fs-5">Reviews Posted</p>
            </div>
        </div>
    </div>
    
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h5 class="fw-bold mb-3">Admin Actions</h5>
            <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    Upload a new PDF book, add cover image, and details.
                    <a href="upload_book.php" class="btn btn-sm btn-primary rounded-pill">Upload <i class="fa-solid fa-arrow-right"></i></a>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    Edit existing books, modify details, or remove books.
                    <a href="manage_books.php" class="btn btn-sm btn-outline-secondary rounded-pill">Manage <i class="fa-solid fa-arrow-right"></i></a>
                </li>
            </ul>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
