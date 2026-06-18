<?php
// books/book_details.php
require_once '../config/database.php';
require_once '../includes/header.php';
require_once '../includes/navbar.php';

$book_id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;

if ($book_id === 0) {
    echo "<div class='container mt-5 fade-in'><h3>Book not found.</h3></div>";
    require_once '../includes/footer.php';
    exit;
}

// Fetch book details
$stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
$stmt->execute([$book_id]);
$book = $stmt->fetch();

if (!$book) {
    echo "<div class='container mt-5 fade-in'><h3>Book not found.</h3></div>";
    require_once '../includes/footer.php';
    exit;
}

// Check if book is saved in library
$is_saved = false;
$reading_progress = null;

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    
    $check_lib = $pdo->prepare("SELECT id FROM library WHERE user_id = ? AND book_id = ?");
    $check_lib->execute([$user_id, $book_id]);
    if ($check_lib->rowCount() > 0) {
        $is_saved = true;
    }

    $check_prog = $pdo->prepare("SELECT last_page FROM reading_progress WHERE user_id = ? AND book_id = ?");
    $check_prog->execute([$user_id, $book_id]);
    $prog = $check_prog->fetch();
    if ($prog) {
        $reading_progress = $prog['last_page'];
    }
}

// Fetch Reviews
$rev_stmt = $pdo->prepare("SELECT r.*, u.username, u.profile_image FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.book_id = ? ORDER BY r.created_at DESC");
$rev_stmt->execute([$book_id]);
$reviews = $rev_stmt->fetchAll();

// Calculate average rating
$avg_rating = 0;
if (count($reviews) > 0) {
    $sum = 0;
    foreach($reviews as $r) { $sum += $r['rating']; }
    $avg_rating = round($sum / count($reviews), 1);
}

$cover = !empty($book['cover_image']) ? '../uploads/covers/'.$book['cover_image'] : '../assets/images/placeholder.jpg';
?>

<div class="container fade-in mt-5 mb-5">
    <div class="row">
        <!-- Book Cover -->
        <div class="col-md-4 mb-4 text-center">
            <img src="<?php echo htmlspecialchars($cover); ?>" alt="Cover" class="img-fluid rounded shadow-lg" style="max-height: 500px; object-fit: cover;">
            
            <div class="mt-4 d-grid gap-2">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="reader.php?id=<?php echo $book['id']; ?>" class="btn btn-primary btn-lg rounded-pill fw-bold shadow-sm">
                        <i class="fa-solid fa-book-open"></i> 
                        <?php echo $reading_progress ? "Continue Reading (Pg $reading_progress)" : "Read Book"; ?>
                    </a>
                    
                    <button id="save-book-btn" class="btn btn-outline-secondary btn-lg rounded-pill fw-bold" data-id="<?php echo $book['id']; ?>">
                        <?php if($is_saved): ?>
                            <i class="fa-solid fa-bookmark text-success"></i> Saved in Library
                        <?php else: ?>
                            <i class="fa-regular fa-bookmark"></i> Save to Library
                        <?php endif; ?>
                    </button>
                    <div id="save-msg" class="text-success small mt-1 d-none">Saved!</div>
                <?php else: ?>
                    <a href="../auth/login.php" class="btn btn-primary btn-lg rounded-pill fw-bold shadow-sm"><i class="fa-solid fa-lock"></i> Login to Read</a>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Book Details -->
        <div class="col-md-8">
            <h1 class="display-5 fw-bold mb-2"><?php echo htmlspecialchars($book['title']); ?></h1>
            <h4 class="text-muted mb-3"><i class="fa-solid fa-pen-nib"></i> By <?php echo htmlspecialchars($book['author']); ?></h4>
            
            <div class="d-flex align-items-center mb-4 gap-3">
                <span class="badge bg-secondary fs-6 rounded-pill px-3"><?php echo htmlspecialchars($book['category']); ?></span>
                <span class="text-warning fs-5">
                    <?php 
                    for($i=1; $i<=5; $i++) {
                        echo $i <= round($avg_rating) ? '<i class="fa-solid fa-star"></i>' : '<i class="fa-regular fa-star"></i>';
                    }
                    ?>
                    <span class="text-muted ms-1 text-sm">(<?php echo $avg_rating; ?>/5)</span>
                </span>
            </div>
            
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body">
                    <h5 class="fw-bold border-bottom pb-2">Description</h5>
                    <p class="card-text lh-lg" style="white-space: pre-wrap;"><?php echo htmlspecialchars($book['description']); ?></p>
                </div>
            </div>

            <!-- Reviews Section -->
            <div class="mt-5">
                <h4 class="fw-bold border-bottom pb-2 mb-4"><i class="fa-regular fa-comments text-primary me-2"></i> Reviews</h4>
                
                <?php if(isset($_SESSION['user_id'])): ?>
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body">
                            <h6 class="fw-bold">Write a Review</h6>
                            <form action="../reviews/add_review.php" method="POST">
                                <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                                <div class="mb-2">
                                    <label class="form-label small text-muted">Rating</label>
                                    <select name="rating" class="form-select form-select-sm w-auto rounded-pill" required>
                                        <option value="5">⭐⭐⭐⭐⭐ Excellent (5)</option>
                                        <option value="4">⭐⭐⭐⭐ Good (4)</option>
                                        <option value="3">⭐⭐⭐ Okay (3)</option>
                                        <option value="2">⭐⭐ Poor (2)</option>
                                        <option value="1">⭐ Terrible (1)</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <textarea name="review_text" class="form-control rounded" rows="3" placeholder="What did you think of the book?" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-sm btn-primary rounded-pill px-4">Submit Review</button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="reviews-list">
                    <?php if(count($reviews) > 0): ?>
                        <?php foreach($reviews as $r): ?>
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-body py-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <div class="fw-bold"><i class="fa-solid fa-user-circle text-muted"></i> <?php echo htmlspecialchars($r['username']); ?></div>
                                        <div class="text-warning small">
                                            <?php 
                                            for($i=1; $i<=5; $i++) {
                                                echo $i <= $r['rating'] ? '<i class="fa-solid fa-star"></i>' : '<i class="fa-regular fa-star"></i>';
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    <p class="card-text mb-1 fst-italic text-muted">"<?php echo htmlspecialchars($r['review_text']); ?>"</p>
                                    <small class="text-muted" style="font-size: 0.75rem;"><?php echo date('M j, Y', strtotime($r['created_at'])); ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted text-center py-4 bg-light rounded shadow-sm">No reviews yet. Be the first to review!</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const saveBtn = document.getElementById('save-book-btn');
    if(saveBtn) {
        saveBtn.addEventListener('click', function() {
            const bookId = this.getAttribute('data-id');
            fetch('../library/ajax_save_book.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `book_id=${bookId}`
            })
            .then(response => response.json())
            .then(data => {
                if(data.status === 'added') {
                    saveBtn.innerHTML = '<i class="fa-solid fa-bookmark text-success"></i> Saved in Library';
                    document.getElementById('save-msg').textContent = 'Added to library!';
                } else if(data.status === 'removed') {
                    saveBtn.innerHTML = '<i class="fa-regular fa-bookmark"></i> Save to Library';
                    document.getElementById('save-msg').textContent = 'Removed from library!';
                }
                const msg = document.getElementById('save-msg');
                msg.classList.remove('d-none');
                setTimeout(() => msg.classList.add('d-none'), 3000);
            })
            .catch(error => console.error('Error:', error));
        });
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>
