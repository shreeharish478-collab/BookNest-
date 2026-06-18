<?php
// includes/footer.php
?>
    <footer class="bg-dark text-light py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> BookNest. All rights reserved.</p>
            <div class="mt-2">
                <a href="#" class="text-light me-3 text-decoration-none hover-primary"><i class="fa-brands fa-twitter"></i></a>
                <a href="#" class="text-light me-3 text-decoration-none hover-primary"><i class="fa-brands fa-facebook"></i></a>
                <a href="#" class="text-light text-decoration-none hover-primary"><i class="fa-brands fa-instagram"></i></a>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script>const BASE_URL = '<?php echo $base_url; ?>';</script>
    <script src="<?php echo $base_url; ?>/assets/js/script.js"></script>
</body>
</html>
