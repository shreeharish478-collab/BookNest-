<?php
// admin/upload_book.php
require_once '../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != 1) {
    die("Access Denied.");
}

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $category = trim($_POST['category']);
    $description = trim($_POST['description']);
    
    $cover_name = '';
    $pdf_name = '';
    
    // File upload directories
    $cover_dir = '../uploads/covers/';
    $pdf_dir = '../uploads/books/';
    
    if(empty($title) || empty($author) || empty($category)) {
        $error = "Title, Author, and Category are required.";
    } else {
        // Handle Cover Image
        if(isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] == 0) {
            $ext = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
            if(in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'webp'])) {
                $cover_name = time() . '_' . uniqid() . '.' . $ext;
                move_uploaded_file($_FILES['cover_image']['tmp_name'], $cover_dir . $cover_name);
            } else {
                $error = "Invalid cover image format.";
            }
        }
        
        // Handle PDF File
        if(isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] == 0 && empty($error)) {
            $ext = pathinfo($_FILES['pdf_file']['name'], PATHINFO_EXTENSION);
            if(strtolower($ext) == 'pdf') {
                $pdf_name = time() . '_' . uniqid() . '.pdf';
                move_uploaded_file($_FILES['pdf_file']['tmp_name'], $pdf_dir . $pdf_name);
            } else {
                $error = "Only PDF files are allowed.";
            }
        } elseif(empty($error)) {
            $error = "PDF file is required.";
        }
        
        // Database Insert
        if(empty($error)) {
            $stmt = $pdo->prepare("INSERT INTO books (title, author, description, category, cover_image, pdf_file) VALUES (?, ?, ?, ?, ?, ?)");
            if($stmt->execute([$title, $author, $description, $category, $cover_name, $pdf_name])) {
                $success = "Book uploaded successfully!";
            } else {
                $error = "Database insertion failed.";
            }
        }
    }
}

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="container mt-5 mb-5 fade-in">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-lg">
                <div class="card-header bg-primary text-white py-3">
                    <h4 class="mb-0 fw-bold"><i class="fa-solid fa-cloud-arrow-up me-2"></i> Upload New Book</h4>
                </div>
                <div class="card-body p-4">
                    <?php if ($error): ?>
                        <div class="alert alert-danger shadow-sm"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="alert alert-success shadow-sm"><?php echo htmlspecialchars($success); ?> <a href="manage_books.php">View books</a></div>
                    <?php endif; ?>

                    <form action="" method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Book Title</label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Author</label>
                                <input type="text" name="author" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Category</label>
                            <select name="category" class="form-select" required>
                                <option value="">Select Category...</option>
                                <option value="Fiction">Fiction</option>
                                <option value="Science">Science</option>
                                <option value="Technology">Technology</option>
                                <option value="History">History</option>
                                <option value="Biography">Biography</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="description" class="form-control" rows="4"></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-semibold">Cover Image (JPG, PNG)</label>
                                <input type="file" name="cover_image" class="form-control" accept="image/*" required>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-semibold">Book File (PDF only)</label>
                                <input type="file" name="pdf_file" class="form-control" accept=".pdf" required>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="dashboard.php" class="btn btn-outline-secondary rounded-pill px-4">Cancel</a>
                            <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">Upload Book</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
