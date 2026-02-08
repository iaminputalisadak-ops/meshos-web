<?php
/**
 * Admin Products Management API
 * CRUD operations for products (requires admin authentication)
 */

require_once '../../config/database.php';
require_once '../../config/cors.php';
require_once '../../config/error_handler.php';

// Check admin authentication
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized - Admin login required'
    ]);
    exit;
}

$conn = getDBConnection();

try {
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            handleGetProducts($conn);
            break;
        case 'POST':
            handleCreateProduct($conn);
            break;
        case 'PUT':
            handleUpdateProduct($conn);
            break;
        case 'DELETE':
            handleDeleteProduct($conn);
            break;
        default:
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'message' => 'Method not allowed'
            ]);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error',
        'error' => $e->getMessage()
    ]);
} finally {
    closeDBConnection($conn);
}

/**
 * Get all products (admin view)
 */
function handleGetProducts($conn) {
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
    $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
    
    $stmt = $conn->prepare("
        SELECT 
            p.*,
            c.name as category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        ORDER BY p.created_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->bind_param("ii", $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'data' => $products,
        'total' => count($products)
    ]);
    
    $stmt->close();
}

/**
 * Create new product
 */
function handleCreateProduct($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $required = ['name', 'category_id', 'price', 'original_price'];
    foreach ($required as $field) {
        if (!isset($data[$field])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => "Field '$field' is required"
            ]);
            return;
        }
    }
    
    $name = $data['name'];
    $categoryId = intval($data['category_id']);
    $price = floatval($data['price']);
    $originalPrice = floatval($data['original_price']);
    $discount = isset($data['discount']) ? intval($data['discount']) : 0;
    $image = isset($data['image']) ? $data['image'] : '';
    $description = isset($data['description']) ? $data['description'] : '';
    $rating = isset($data['rating']) ? floatval($data['rating']) : 0.0;
    $reviews = isset($data['reviews']) ? intval($data['reviews']) : 0;
    $inStock = isset($data['in_stock']) ? (bool)$data['in_stock'] : true;
    
    $stmt = $conn->prepare("
        INSERT INTO products (name, category_id, price, original_price, discount, image, description, rating, reviews, in_stock)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("sidddssdii", $name, $categoryId, $price, $originalPrice, $discount, $image, $description, $rating, $reviews, $inStock);
    
    if ($stmt->execute()) {
        $productId = $conn->insert_id;
        
        // Add product images if provided
        if (isset($data['images']) && is_array($data['images'])) {
            $imageStmt = $conn->prepare("
                INSERT INTO product_images (product_id, image_url, is_primary, display_order)
                VALUES (?, ?, ?, ?)
            ");
            
            foreach ($data['images'] as $index => $imageUrl) {
                $isPrimary = $index === 0 ? 1 : 0;
                $imageStmt->bind_param("isii", $productId, $imageUrl, $isPrimary, $index);
                $imageStmt->execute();
            }
            $imageStmt->close();
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Product created successfully',
            'data' => ['id' => $productId]
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to create product',
            'error' => $conn->error
        ]);
    }
    
    $stmt->close();
}

/**
 * Update product
 */
function handleUpdateProduct($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['id'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Product ID is required'
        ]);
        return;
    }
    
    $productId = intval($data['id']);
    $updates = [];
    $params = [];
    $types = "";
    
    $fields = ['name', 'category_id', 'price', 'original_price', 'discount', 'image', 'description', 'rating', 'reviews', 'in_stock'];
    
    foreach ($fields as $field) {
        if (isset($data[$field])) {
            $updates[] = "$field = ?";
            if ($field === 'category_id' || $field === 'discount' || $field === 'reviews') {
                $params[] = intval($data[$field]);
                $types .= "i";
            } elseif ($field === 'price' || $field === 'original_price' || $field === 'rating') {
                $params[] = floatval($data[$field]);
                $types .= "d";
            } elseif ($field === 'in_stock') {
                $params[] = (bool)$data[$field] ? 1 : 0;
                $types .= "i";
            } else {
                $params[] = $data[$field];
                $types .= "s";
            }
        }
    }
    
    if (empty($updates)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'No fields to update'
        ]);
        return;
    }
    
    $params[] = $productId;
    $types .= "i";
    
    $query = "UPDATE products SET " . implode(", ", $updates) . " WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Product updated successfully'
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update product',
            'error' => $conn->error
        ]);
    }
    
    $stmt->close();
}

/**
 * Delete product
 */
function handleDeleteProduct($conn) {
    $productId = isset($_GET['id']) ? intval($_GET['id']) : null;
    
    if (!$productId) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Product ID is required'
        ]);
        return;
    }
    
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $productId);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Product deleted successfully'
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to delete product',
            'error' => $conn->error
        ]);
    }
    
    $stmt->close();
}
?>


