<?php
// admin/manage_books.php
require_once '../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != 1) {
    die("Access Denied.");
}

$msg = '';

// Handle Deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id_to_delete = (int)$_GET['delete'];
    
    // Get file names to delete them from server
    $stmt = $pdo->prepare("SELECT cover_image, pdf_file FROM books WHERE id = ?");
    $stmt->execute([$id_to_delete]);
    $book = $stmt->fetch();
    
    if ($book) {
        if (!empty($book['cover_image']) && file_exists('../uploads/covers/'.$book['cover_image'])) {
            unlink('../uploads/covers/'.$book['cover_image']);
        }
        if (!empty($book['pdf_file']) && file_exists('../uploads/books/'.$book['pdf_file'])) {
            unlink('../uploads/books/'.$book['pdf_file']);
        }
        
        $del = $pdo->prepare("DELETE FROM books WHERE id = ?");
        $del->execute([$id_to_delete]);
        $msg = "Book deleted successfully.";
    }
}

// Fetch all books
$stmt = $pdo->query("SELECT * FROM books ORDER BY uploaded_at DESC");
$books = $stmt->fetchAll();

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="container mt-5 mb-5 fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-2">
        <h2 class="fw-bold"><i class="fa-solid fa-list-check text-primary me-2"></i> Manage Books</h2>
        <a href="dashboard.php" class="btn btn-outline-secondary rounded-pill"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>
    </div>

    <?php if ($msg): ?>
        <div class="alert alert-success shadow-sm"><?php echo htmlspecialchars($msg); ?></div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="ps-4">ID</th>
                            <th scope="col">Cover</th>
                            <th scope="col">Title</th>
                            <th scope="col">Author</th>
                            <th scope="col">Category</th>
                            <th scope="col" class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($books) > 0): ?>
                            <?php foreach($books as $book): ?>
                                <tr>
                                    <td class="ps-4 text-muted fw-bold">#<?php echo $book['id']; ?></td>
                                    <td>
                                        <?php $cover = !empty($book['cover_image']) ? '../uploads/covers/'.$book['cover_image'] : '../assets/images/placeholder.jpg'; ?>
                                        <img src="<?php echo htmlspecialchars($cover); ?>" alt="cover" class="rounded shadow-sm object-fit-cover" style="width: 50px; height: 75px;">
                                    </td>
                                    <td class="fw-semibold"><?php echo htmlspecialchars($book['title']); ?></td>
                                    <td><?php echo htmlspecialchars($book['author']); ?></td>
                                    <td><span class="badge bg-secondary rounded-pill"><?php echo htmlspecialchars($book['category']); ?></span></td>
                                    <td class="text-end pe-4">
                                        <a href="../books/book_details.php?id=<?php echo $book['id']; ?>" class="btn btn-sm btn-outline-primary rounded-circle" title="View"><i class="fa-solid fa-eye"></i></a>
                                        <a href="?delete=<?php echo $book['id']; ?>" class="btn btn-sm btn-outline-danger rounded-circle ms-1" onclick="return confirm('Are you sure you want to delete this book? This will erase reviews, progress, and files permanently.');" title="Delete"><i class="fa-solid fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No books found in the database. <a href="upload_book.php">Upload one now</a>.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
