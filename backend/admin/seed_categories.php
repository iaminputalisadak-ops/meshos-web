<?php
/**
 * Seed Categories - Add default categories to database
 * Run this: http://localhost/backend/admin/seed_categories.php
 */

session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

require_once '../config/database.php';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Seed Categories</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        .box { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: #4CAF50; padding: 15px; background: #e8f5e9; border-radius: 5px; margin: 10px 0; }
        .error { color: #f44336; padding: 15px; background: #ffebee; border-radius: 5px; margin: 10px 0; }
        .info { color: #2196F3; padding: 15px; background: #e3f2fd; border-radius: 5px; margin: 10px 0; }
        .btn { display: inline-block; padding: 12px 24px; background: #667eea; color: white; text-decoration: none; border-radius: 8px; margin: 10px 5px; }
        @media (max-width: 768px) {
            body { padding: 10px; margin: 20px auto; }
            .box { padding: 20px; }
            .btn { display: block; width: 100%; margin: 10px 0; text-align: center; }
        }
    </style>
</head>
<body>
    <div class="box">
        <h1>🌱 Seed Categories</h1>
        
        <?php
        try {
            $conn = getDBConnection();
            
            $categories = [
                ['Popular', 'popular', '⭐', 'https://images.unsplash.com/photo-1513151233558-d860c5398176?w=200&q=80'],
                ['Kurti, Saree & Lehenga', 'kurti-saree-lehenga', '👗', 'https://images.unsplash.com/photo-1594633312681-425c7b97ccd1?w=200&q=80'],
                ['Women Western', 'women-western', '👚', 'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=200&q=80'],
                ['Lingerie', 'lingerie', '👙', 'https://images.unsplash.com/photo-1583496661160-fb588827e1a3?w=200&q=80'],
                ['Men', 'men', '👔', 'https://images.unsplash.com/photo-1617137968427-85924c800a22?w=200&q=80'],
                ['Kids & Toys', 'kids-toys', '👶', 'https://images.unsplash.com/photo-1555252333-9f8e92e65df9?w=200&q=80'],
                ['Home & Kitchen', 'home-kitchen', '🏠', 'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=200&q=80'],
                ['Beauty & Health', 'beauty-health', '💄', 'https://images.unsplash.com/photo-1596462502278-27bfdc403348?w=200&q=80'],
                ['Jewellery & Accessories', 'jewellery-accessories', '💍', 'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?w=200&q=80'],
                ['Bags & Footwear', 'bags-footwear', '👜', 'https://images.unsplash.com/photo-1544966503-7cc5ac882d5f?w=200&q=80'],
                ['Electronics', 'electronics', '📱', 'https://images.unsplash.com/photo-1468495244123-6c6c332eeece?w=200&q=80'],
            ];
            
            $inserted = 0;
            $updated = 0;
            $skipped = 0;
            
            // First, check if icon and image columns exist
            $hasIcon = false;
            $hasImage = false;
            $result = $conn->query("SHOW COLUMNS FROM categories LIKE 'icon'");
            if ($result && $result->num_rows > 0) {
                $hasIcon = true;
            }
            $result = $conn->query("SHOW COLUMNS FROM categories LIKE 'image'");
            if ($result && $result->num_rows > 0) {
                $hasImage = true;
            }
            
            // Add columns if they don't exist
            if (!$hasIcon) {
                $conn->query("ALTER TABLE categories ADD COLUMN icon VARCHAR(50) DEFAULT NULL");
                echo "<div class='info'>ℹ️ Added 'icon' column to categories table</div>";
            }
            if (!$hasImage) {
                $conn->query("ALTER TABLE categories ADD COLUMN image TEXT DEFAULT NULL");
                echo "<div class='info'>ℹ️ Added 'image' column to categories table</div>";
            }
            
            foreach ($categories as $cat) {
                $name = $cat[0];
                $slug = $cat[1];
                $icon = $cat[2];
                $image = $cat[3];
                
                // Check if category exists
                $check = $conn->prepare("SELECT id FROM categories WHERE slug = ? OR name = ?");
                if (!$check) {
                    echo "<div class='error'>❌ Error preparing check query: " . $conn->error . "</div>";
                    continue;
                }
                $check->bind_param("ss", $slug, $name);
                $check->execute();
                $result = $check->get_result();
                
                if ($result->num_rows > 0) {
                    // Update existing
                    $update = $conn->prepare("UPDATE categories SET name = ?, icon = ?, image = ? WHERE slug = ?");
                    if (!$update) {
                        echo "<div class='error'>❌ Error preparing update query: " . $conn->error . "</div>";
                        $check->close();
                        continue;
                    }
                    $update->bind_param("ssss", $name, $icon, $image, $slug);
                    if ($update->execute()) {
                        $updated++;
                        echo "<div class='info'>✅ Updated: $name</div>";
                    } else {
                        echo "<div class='error'>❌ Error updating $name: " . $update->error . "</div>";
                    }
                    $update->close();
                } else {
                    // Insert new
                    $insert = $conn->prepare("INSERT INTO categories (name, slug, icon, image) VALUES (?, ?, ?, ?)");
                    if (!$insert) {
                        echo "<div class='error'>❌ Error preparing insert query: " . $conn->error . "</div>";
                        $check->close();
                        continue;
                    }
                    $insert->bind_param("ssss", $name, $slug, $icon, $image);
                    if ($insert->execute()) {
                        $inserted++;
                        echo "<div class='success'>✅ Added: $name</div>";
                    } else {
                        echo "<div class='error'>❌ Error inserting $name: " . $insert->error . "</div>";
                    }
                    $insert->close();
                }
                $check->close();
            }
            
            echo "<div class='success'>
                <h2>🎉 Categories Seeded!</h2>
                <p>Inserted: $inserted | Updated: $updated | Skipped: $skipped</p>
                <p><a href='categories.php' class='btn'>View Categories</a></p>
            </div>";
            
            $conn->close();
        } catch (Exception $e) {
            echo "<div class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
        ?>
    </div>
</body>
</html>

