</div> <!-- Close container div from header -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> School Management System. All rights reserved.</p>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>

/* Example usage in your main PHP file (like index.php) */
<?php
define('INCLUDED', true);
include 'header.php';
?>

<!-- Your main content here -->
<h1>Welcome to School Management System</h1>

<?php
include 'footer.php';
?>