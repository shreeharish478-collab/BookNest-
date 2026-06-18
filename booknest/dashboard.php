<?php
include("config.php");

if(!isset($_SESSION['user'])){
    header("Location: login.php");
    exit();
}
?>

<h2>Welcome, <?php echo $_SESSION['user']; ?> 🎉</h2>

<p>This is your dashboard.</p>

<a href="logout.php">Logout</a>
