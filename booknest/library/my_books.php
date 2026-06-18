<?php
// library/my_books.php
require_once '../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch saved books from library
$sql = "SELECT b.*, l.saved_at FROM library l JOIN books b ON l.book_id = b.id WHERE l.user_id = ? ORDER BY l.saved_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$library_books = $stmt->fetchAll();

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="container mt-5 mb-5 fade-in">
    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-4">
        <h2 class="fw-bold"><i class="fa-solid fa-bookmark text-primary me-2"></i> My Library</h2>
        <span class="badge bg-primary rounded-pill"><?php echo count($library_books); ?> Books Saved</span>
    </div>

    <?php if (count($library_books) > 0): ?>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
            <?php foreach ($library_books as $book): ?>
                <div class="col" id="book-card-<?php echo $book['id']; ?>">
                    <div class="card h-100 border-0 shadow-sm text-center position-relative">
                        <!-- Remove from library button -->
                        <button class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 rounded-circle shadow-sm remove-lib-btn" data-id="<?php echo $book['id']; ?>" title="Remove from Library">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                        
                        <?php $cover = !empty($book['cover_image']) ? '../uploads/covers/'.$book['cover_image'] : '../assets/images/placeholder.jpg'; ?>
                        <a href="../books/book_details.php?id=<?php echo $book['id']; ?>">
                            <img src="<?php echo htmlspecialchars($cover); ?>" class="card-img-top book-cover p-2" alt="Cover">
                        </a>
                        
                        <div class="card-body d-flex flex-column">
                            <h6 class="card-title fw-bold text-truncate" title="<?php echo htmlspecialchars($book['title']); ?>">
                                <?php echo htmlspecialchars($book['title']); ?>
                            </h6>
                            <p class="text-muted small mb-3"><?php echo htmlspecialchars($book['author']); ?></p>
                            
                            <div class="mt-auto">
                                <a href="../books/reader.php?id=<?php echo $book['id']; ?>" class="btn btn-sm btn-primary w-100 rounded-pill fw-bold">Read</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <i class="fa-solid fa-swatchbook fa-4x text-muted mb-3 opacity-50"></i>
            <h4 class="fw-bold">Your library is empty.</h4>
            <p class="text-muted">Start browsing and save some books to read later.</p>
            <a href="../books/browse.php" class="btn btn-primary rounded-pill px-4 mt-3">Browse Books</a>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const removeBtns = document.querySelectorAll('.remove-lib-btn');
    removeBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const bookId = this.getAttribute('data-id');
            if(confirm('Remove this book from your library?')) {
                fetch('ajax_save_book.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `book_id=${bookId}`
                }).then(res => res.json()).then(data => {
                    if(data.status === 'removed') {
                        document.getElementById('book-card-' + bookId).remove();
                    }
                });
            }
        });
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
