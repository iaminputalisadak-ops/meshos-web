<?php
/**
 * Database Seeding Script
 * Run this script to populate the database with sample data
 * Usage: php seed_data.php
 */

require_once '../config/database.php';

$conn = getDBConnection();

echo "Starting database seeding...\n";

try {
    // Insert sample products
    $products = [
        [
            'name' => 'Women\'s Sports Bra Set',
            'category' => 'Lingerie',
            'price' => 299,
            'original_price' => 599,
            'discount' => 50,
            'image' => 'https://images.unsplash.com/photo-1583496661160-fb588827e1a3?w=600&q=80',
            'description' => 'Comfortable sports bra set with high support.',
            'rating' => 4.6,
            'reviews' => 234
        ],
        [
            'name' => 'Women\'s Cotton Bra Set - Pack of 3',
            'category' => 'Lingerie',
            'price' => 399,
            'original_price' => 799,
            'discount' => 50,
            'image' => 'https://images.unsplash.com/photo-1594633312681-425c7b97ccd1?w=600&q=80',
            'description' => 'Comfortable cotton bra set with perfect fit.',
            'rating' => 4.5,
            'reviews' => 1234
        ],
        [
            'name' => 'Lace Underwear Set - 5 Pieces',
            'category' => 'Lingerie',
            'price' => 299,
            'original_price' => 599,
            'discount' => 50,
            'image' => 'https://images.unsplash.com/photo-1595777457583-95e059d581b8?w=600&q=80',
            'description' => 'Elegant lace underwear set with comfortable fit.',
            'rating' => 4.6,
            'reviews' => 567
        ],
        [
            'name' => 'Wireless Bra - Seamless Design',
            'category' => 'Lingerie',
            'price' => 249,
            'original_price' => 499,
            'discount' => 50,
            'image' => 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=600&q=80',
            'description' => 'Seamless wireless bra for ultimate comfort.',
            'rating' => 4.4,
            'reviews' => 890
        ],
        [
            'name' => 'Cotton Nightwear Set',
            'category' => 'Lingerie',
            'price' => 349,
            'original_price' => 699,
            'discount' => 50,
            'image' => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=600&q=80',
            'description' => 'Soft cotton nightwear set for comfortable sleep.',
            'rating' => 4.7,
            'reviews' => 234
        ]
    ];
    
    $stmt = $conn->prepare("
        INSERT INTO products (name, category_id, price, original_price, discount, image, description, rating, reviews, in_stock)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
    ");
    
    foreach ($products as $product) {
        // Get category ID
        $categoryStmt = $conn->prepare("SELECT id FROM categories WHERE slug = ?");
        $slug = strtolower(str_replace([' ', '&'], ['-', ''], $product['category']));
        $categoryStmt->bind_param("s", $slug);
        $categoryStmt->execute();
        $categoryResult = $categoryStmt->get_result();
        
        if ($categoryResult->num_rows > 0) {
            $categoryId = $categoryResult->fetch_assoc()['id'];
            
            $stmt->bind_param(
                "sidddssdi",
                $product['name'],
                $categoryId,
                $product['price'],
                $product['original_price'],
                $product['discount'],
                $product['image'],
                $product['description'],
                $product['rating'],
                $product['reviews']
            );
            
            if ($stmt->execute()) {
                $productId = $conn->insert_id;
                
                // Insert product image
                $imageStmt = $conn->prepare("
                    INSERT INTO product_images (product_id, image_url, is_primary, display_order)
                    VALUES (?, ?, 1, 0)
                ");
                $imageStmt->bind_param("is", $productId, $product['image']);
                $imageStmt->execute();
                $imageStmt->close();
                
                echo "Inserted product: {$product['name']}\n";
            } else {
                echo "Error inserting product: {$product['name']} - " . $conn->error . "\n";
            }
        } else {
            echo "Category not found: {$product['category']}\n";
        }
        
        $categoryStmt->close();
    }
    
    $stmt->close();
    
    echo "Database seeding completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} finally {
    closeDBConnection($conn);
}
?>


