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
// Handle logout from any page
if (isset($_GET['logout'])) {
    // Clear all session data
    $_SESSION = array();
    
    // Destroy the session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    // Destroy the session
    session_destroy();
    
    // Redirect to login page
    header('Location: index.php');
    exit;
}
?>

