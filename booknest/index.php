<?php
// index.php
require_once 'config/database.php';
require_once 'includes/header.php';
require_once 'includes/navbar.php';

// Fetch Trending Books (highest rated or most recently added)
$stmt = $pdo->query("SELECT * FROM books ORDER BY uploaded_at DESC LIMIT 4");
$trending_books = $stmt->fetchAll();

// Fetch Top Readers
$stmt = $pdo->query("SELECT username, books_read_count FROM users ORDER BY books_read_count DESC LIMIT 3");
$top_readers = $stmt->fetchAll();
?>

<div class="container fade-in">
    <!-- Hero Section -->
    <div class="hero-section text-center shadow-lg position-relative overflow-hidden mb-5">
        <h1 class="display-4 fw-bold mb-3"><i class="fa-solid fa-book-open"></i> Discover Your Next Great Read</h1>
        <p class="lead mb-4 opacity-75">Join thousands of readers on BookNest. Read online, track progress, and write reviews.</p>
        <?php if(!isset($_SESSION['user_id'])): ?>
            <a href="auth/signup.php" class="btn btn-light btn-lg rounded-pill fw-bold text-primary shadow px-5">Get Started</a>
        <?php else: ?>
            <a href="books/browse.php" class="btn btn-light btn-lg rounded-pill fw-bold text-primary shadow px-5">Browse Library</a>
        <?php endif; ?>
    </div>

    <!-- Featured Categories -->
    <h3 class="fw-bold mb-4 border-bottom pb-2"><i class="fa-solid fa-layer-group text-primary me-2"></i> Categories</h3>
    <div class="row mb-5 text-center">
        <?php 
        $categories = ['Fiction' => 'fa-book-skull', 'Science' => 'fa-flask', 'Technology' => 'fa-laptop-code', 'History' => 'fa-monument'];
        foreach($categories as $cat => $icon): ?>
        <div class="col-6 col-md-3 mb-3">
            <a href="books/browse.php?category=<?php echo urlencode($cat); ?>" class="text-decoration-none">
                <div class="card bg-primary text-white h-100 border-0 shadow-sm py-4 custom-hover">
                    <div class="card-body">
                        <i class="fa-solid <?php echo $icon; ?> fa-2x mb-2"></i>
                        <h5 class="fw-bold mb-0"><?php echo $cat; ?></h5>
                    </div>
                </div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="row mb-5">
        <!-- Trending Books -->
        <div class="col-lg-8">
            <h3 class="fw-bold mb-4 border-bottom pb-2"><i class="fa-solid fa-fire text-danger me-2"></i> Trending Books</h3>
            <div class="row g-4">
                <?php if(count($trending_books) > 0): ?>
                    <?php foreach($trending_books as $book): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="row g-0">
                                <div class="col-4">
                                    <?php $cover = !empty($book['cover_image']) ? 'uploads/covers/'.$book['cover_image'] : 'assets/images/placeholder.jpg'; ?>
                                    <img src="<?php echo htmlspecialchars($cover); ?>" class="img-fluid rounded-start h-100 object-fit-cover" alt="Cover" style="min-height: 180px;">
                                </div>
                                <div class="col-8">
                                    <div class="card-body d-flex flex-column h-100">
                                        <h5 class="card-title fw-bold text-truncate" title="<?php echo htmlspecialchars($book['title']); ?>"><?php echo htmlspecialchars($book['title']); ?></h5>
                                        <p class="card-text text-muted small mb-2"><i class="fa-solid fa-pen-nib"></i> <?php echo htmlspecialchars($book['author']); ?></p>
                                        <div class="mt-auto">
                                            <a href="books/book_details.php?id=<?php echo $book['id']; ?>" class="btn btn-sm btn-outline-primary rounded-pill w-100">View Details</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted">No books uploaded yet.</p>
                <?php endif; ?>
            </div>
            <div class="text-end mt-2">
                <a href="books/browse.php" class="btn btn-link text-decoration-none fw-bold">View All Books <i class="fa-solid fa-arrow-right"></i></a>
            </div>
        </div>

        <!-- Top Readers Leaderboard -->
        <div class="col-lg-4">
            <h3 class="fw-bold mb-4 border-bottom pb-2"><i class="fa-solid fa-trophy text-warning me-2"></i> Top Readers</h3>
            <div class="card border-0 shadow-sm">
                <ul class="list-group list-group-flush rounded">
                    <?php if(count($top_readers) > 0): ?>
                        <?php foreach($top_readers as $index => $reader): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                <div class="d-flex align-items-center">
                                    <div class="me-3 fs-5 fw-bold <?php echo $index == 0 ? 'text-warning' : ($index == 1 ? 'text-secondary' : 'text-danger'); ?>">
                                        #<?php echo $index + 1; ?>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($reader['username']); ?></h6>
                                    </div>
                                </div>
                                <span class="badge bg-primary rounded-pill"><?php echo $reader['books_read_count']; ?> books</span>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="list-group-item text-muted">No reading data available.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
.custom-hover { transition: transform 0.2s, box-shadow 0.2s; }
.custom-hover:hover { transform: translateY(-5px); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }
</style>

<?php require_once 'includes/footer.php'; ?>
