<?php
// includes/navbar.php
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top shadow-sm custom-navbar">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center" href="<?php echo $base_url; ?>/index.php">
            <i class="fa-solid fa-book-open-reader me-2"></i> BookNest
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <!-- Search Bar -->
            <form class="d-flex mx-auto position-relative search-form my-2 my-lg-0" action="<?php echo $base_url; ?>/books/browse.php" method="GET">
                <input class="form-control rounded-pill pe-5" type="search" placeholder="Search books, authors..." aria-label="Search" name="q" id="global-search" autocomplete="off">
                <button class="btn position-absolute end-0 top-50 translate-middle-y rounded-circle text-muted" type="submit">
                    <i class="fa-solid fa-search"></i>
                </button>
                <!-- Search Results Dropdown -->
                <div id="search-results-dropdown" class="list-group position-absolute w-100 shadow d-none" style="top: 100%; z-index: 1000;"></div>
            </form>
            
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $base_url; ?>/index.php"><i class="fa-solid fa-home me-1"></i> Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $base_url; ?>/books/browse.php"><i class="fa-solid fa-book me-1"></i> Browse</a>
                </li>
                
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $base_url; ?>/library/my_books.php"><i class="fa-solid fa-bookmark me-1"></i> My Library</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fa-solid fa-user me-1"></i> <?php echo htmlspecialchars($_SESSION['username'] ?? 'Profile'); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                            <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] == 1): // Simple admin check ?>
                                <li><a class="dropdown-item" href="<?php echo $base_url; ?>/admin/dashboard.php"><i class="fa-solid fa-gauge me-2 text-primary"></i> Admin Panel</a></li>
                                <li><hr class="dropdown-divider"></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item text-danger" href="<?php echo $base_url; ?>/auth/logout.php"><i class="fa-solid fa-sign-out-alt me-2"></i> Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link border border-light rounded px-3 py-1 ms-2" href="<?php echo $base_url; ?>/auth/login.php"><i class="fa-solid fa-sign-in-alt me-1"></i> Login</a>
                    </li>
                <?php endif; ?>
                
                <li class="nav-item ms-2">
                    <button id="theme-toggle" class="btn btn-outline-light rounded-circle theme-btn" title="Toggle Dark Mode">
                        <i class="fa-solid fa-moon"></i>
                    </button>
                </li>
            </ul>
        </div>
    </div>
</nav>
