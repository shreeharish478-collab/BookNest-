<?php
// auth/login.php
require_once '../config/database.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header("Location: ../index.php");
            exit();
        } else {
            $error = "Invalid email or password.";
        }
    }
}

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="container fade-in">
    <div class="auth-wrapper">
        <div class="card auth-card shadow-lg border-0">
            <div class="card-body">
                <div class="text-center mb-4">
                    <h2 class="fw-bold text-primary"><i class="fa-solid fa-sign-in-alt"></i> Welcome Back</h2>
                    <p class="text-muted">Login to continue reading</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger shadow-sm"><i class="fa-solid fa-triangle-exclamation"></i> <?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label text-muted fw-semibold">Email Address</label>
                        <input type="email" name="email" class="form-control rounded-pill px-3 py-2" required
                            value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                    <div class="mb-4">
                        <label class="form-label text-muted fw-semibold">Password</label>
                        <input type="password" name="password" class="form-control rounded-pill px-3 py-2" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 fw-bold shadow-sm">
                        Login to BookNest
                    </button>
                </form>

                <div class="text-center mt-4">
                    <p class="mb-0 text-muted">New here? <a href="signup.php" class="text-primary text-decoration-none fw-bold">Create an account</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
