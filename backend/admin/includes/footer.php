            </div>
            <!-- End Content Area -->
        </div>
        <!-- End Main Content -->
    </div>
    <!-- End Admin Wrapper -->

    <!-- Messages -->
    <div id="message" class="message"></div>

    <!-- Scripts -->
    <script src="assets/js/admin.js"></script>
    <?php if (isset($additionalScripts)): ?>
        <?php foreach ($additionalScripts as $script): ?>
            <script src="<?php echo $script; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>

<?php
// Handle logout
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header('Location: index.php');
    exit;
}
?>

