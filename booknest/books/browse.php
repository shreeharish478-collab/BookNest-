<?php
// books/browse.php
require_once '../config/database.php';
require_once '../includes/header.php';
require_once '../includes/navbar.php';

$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';

// Pagination
$limit = 12;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$where = [];
$params = [];

if ($search) {
    $where[] = "(title LIKE ? OR author LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($category) {
    $where[] = "category = ?";
    $params[] = $category;
}

$whereClause = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";

// Count total items
$countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM books $whereClause");
$countStmt->execute($params);
$totalRows = $countStmt->fetch()['total'];
$totalPages = ceil($totalRows / $limit);

// Fetch items
$sql = "SELECT * FROM books $whereClause ORDER BY title ASC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$books = $stmt->fetchAll();

?>

<div class="container fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4 mt-4 border-bottom pb-2">
        <h2 class="fw-bold"><i class="fa-solid fa-book-open text-primary me-2"></i> Browse Books</h2>
    </div>

    <!-- Filters/Categories -->
    <div class="mb-4">
        <div class="d-flex gap-2 flex-wrap">
            <a href="browse.php" class="btn btn-sm btn-outline-secondary <?php echo empty($category) ? 'active' : ''; ?> rounded-pill">All</a>
            <?php 
            $cats = ['Fiction', 'Science', 'Technology', 'History', 'Romance','Philosophy','Biography','Self-help','Health','Spirituality','Religion','Cooking','Travel','Business','Finance','Entertainment','Humor','Children','Education','Art','Music','Sports','Games','Photography','Design','Fashion','Home','Gardening','DIY','Hobbies','Crafts'];
            foreach($cats as $cat): ?>
                <a href="browse.php?category=<?php echo urlencode($cat); ?>" class="btn btn-sm btn-outline-secondary <?php echo $category == $cat ? 'active' : ''; ?> rounded-pill"><?php echo $cat; ?></a>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if (count($books) > 0): ?>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4 mb-5">
            <?php foreach ($books as $book): ?>
                <div class="col">
                    <div class="card h-100 border-0 shadow-sm text-center">
                        <?php $cover = !empty($book['cover_image']) ? '../uploads/covers/'.$book['cover_image'] : '../assets/images/placeholder.jpg'; ?>
                        <img src="<?php echo htmlspecialchars($cover); ?>" class="card-img-top book-cover p-2" alt="Cover">
                        <div class="card-body d-flex flex-column">
                            <h6 class="card-title fw-bold text-truncate" title="<?php echo htmlspecialchars($book['title']); ?>">
                                <?php echo htmlspecialchars($book['title']); ?>
                            </h6>
                            <p class="text-muted small mb-2"><?php echo htmlspecialchars($book['author']); ?></p>
                            
                            <div class="mt-auto d-flex justify-content-between">
                                <a href="book_details.php?id=<?php echo $book['id']; ?>" class="btn btn-sm btn-primary w-100 rounded-pill fw-bold">Read</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo $search ? '&q='.urlencode($search) : ''; ?><?php echo $category ? '&category='.urlencode($category) : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>

    <?php else: ?>
        <div class="text-center py-5">
            <i class="fa-solid fa-magnifying-glass fa-3x text-muted mb-3"></i>
            <h4 class="fw-bold">No books found</h4>
            <p class="text-muted">Try adjusting your search or filters.</p>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
