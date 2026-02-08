<?php
/**
 * Products API Endpoint
 * Handles GET requests for products
 */

require_once '../config/database.php';
require_once '../config/cors.php';
require_once '../config/error_handler.php';

$conn = getDBConnection();

try {
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            handleGetProducts($conn);
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
 * Handle GET request for products
 */
function handleGetProducts($conn) {
    $category = isset($_GET['category']) ? $_GET['category'] : null;
    $categoryId = isset($_GET['category_id']) ? intval($_GET['category_id']) : null;
    $productId = isset($_GET['id']) ? intval($_GET['id']) : null;
    $search = isset($_GET['search']) ? $_GET['search'] : null;
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
    $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
    
    // Get single product by ID
    if ($productId) {
        getProductById($conn, $productId);
        return;
    }
    
    // Get products by category
    if ($category || $categoryId) {
        getProductsByCategory($conn, $category, $categoryId, $search, $limit, $offset);
        return;
    }
    
    // Get all products
    getAllProducts($conn, $search, $limit, $offset);
}

/**
 * Get product by ID
 */
function getProductById($conn, $productId) {
    $stmt = $conn->prepare("
        SELECT 
            p.*,
            c.name as category_name,
            c.slug as category_slug,
            GROUP_CONCAT(pi.image_url ORDER BY pi.is_primary DESC, pi.display_order) as images
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN product_images pi ON p.id = pi.product_id
        WHERE p.id = ?
        GROUP BY p.id
    ");
    
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Product not found'
        ]);
        return;
    }
    
    $product = $result->fetch_assoc();
    
    // Process images
    if ($product['images']) {
        $product['images'] = explode(',', $product['images']);
    } else {
        $product['images'] = $product['image'] ? [$product['image']] : [];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $product
    ]);
    
    $stmt->close();
}

/**
 * Get products by category
 */
function getProductsByCategory($conn, $category, $categoryId, $search, $limit, $offset) {
    $query = "
        SELECT 
            p.*,
            c.name as category_name,
            c.slug as category_slug,
            GROUP_CONCAT(pi.image_url ORDER BY pi.is_primary DESC, pi.display_order) as images
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN product_images pi ON p.id = pi.product_id
        WHERE 1=1
    ";
    
    $params = [];
    $types = "";
    
    if ($categoryId) {
        $query .= " AND p.category_id = ?";
        $params[] = $categoryId;
        $types .= "i";
    } elseif ($category) {
        $query .= " AND c.slug = ?";
        $params[] = $category;
        $types .= "s";
    }
    
    if ($search) {
        $query .= " AND (p.name LIKE ? OR p.description LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= "ss";
    }
    
    $query .= " GROUP BY p.id ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";
    
    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    $products = [];
    while ($row = $result->fetch_assoc()) {
        // Process images
        if ($row['images']) {
            $row['images'] = explode(',', $row['images']);
        } else {
            $row['images'] = $row['image'] ? [$row['image']] : [];
        }
        $products[] = $row;
    }
    
    // Get total count
    $countQuery = "
        SELECT COUNT(DISTINCT p.id) as total
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE 1=1
    ";
    
    if ($categoryId) {
        $countQuery .= " AND p.category_id = $categoryId";
    } elseif ($category) {
        $category = $conn->real_escape_string($category);
        $countQuery .= " AND c.slug = '$category'";
    }
    
    if ($search) {
        $search = $conn->real_escape_string($search);
        $countQuery .= " AND (p.name LIKE '%$search%' OR p.description LIKE '%$search%')";
    }
    
    $countResult = $conn->query($countQuery);
    $total = $countResult->fetch_assoc()['total'];
    
    echo json_encode([
        'success' => true,
        'data' => $products,
        'total' => $total,
        'limit' => $limit,
        'offset' => $offset
    ]);
    
    $stmt->close();
}

/**
 * Get all products
 */
function getAllProducts($conn, $search, $limit, $offset) {
    $query = "
        SELECT 
            p.*,
            c.name as category_name,
            c.slug as category_slug,
            GROUP_CONCAT(pi.image_url ORDER BY pi.is_primary DESC, pi.display_order) as images
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN product_images pi ON p.id = pi.product_id
        WHERE 1=1
    ";
    
    $params = [];
    $types = "";
    
    if ($search) {
        $query .= " AND (p.name LIKE ? OR p.description LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= "ss";
    }
    
    $query .= " GROUP BY p.id ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";
    
    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    $products = [];
    while ($row = $result->fetch_assoc()) {
        // Process images
        if ($row['images']) {
            $row['images'] = explode(',', $row['images']);
        } else {
            $row['images'] = $row['image'] ? [$row['image']] : [];
        }
        $products[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'data' => $products,
        'total' => count($products),
        'limit' => $limit,
        'offset' => $offset
    ]);
    
    $stmt->close();
}
?>


