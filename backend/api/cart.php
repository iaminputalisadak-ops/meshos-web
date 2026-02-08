<?php
/**
 * Cart API Endpoint
 * Handles cart operations (GET, POST, PUT, DELETE)
 */

require_once '../config/database.php';
require_once '../config/cors.php';
require_once '../config/error_handler.php';

$conn = getDBConnection();

try {
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            handleGetCart($conn);
            break;
        case 'POST':
            handleAddToCart($conn);
            break;
        case 'PUT':
            handleUpdateCart($conn);
            break;
        case 'DELETE':
            handleRemoveFromCart($conn);
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
 * Get session ID
 */
function getSessionId() {
    if (!isset($_COOKIE['session_id'])) {
        $sessionId = bin2hex(random_bytes(16));
        setcookie('session_id', $sessionId, time() + (86400 * 30), '/'); // 30 days
        return $sessionId;
    }
    return $_COOKIE['session_id'];
}

/**
 * Get cart items
 */
function handleGetCart($conn) {
    $sessionId = getSessionId();
    $userId = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
    
    $query = "
        SELECT 
            c.id,
            c.product_id,
            c.quantity,
            p.name,
            p.price,
            p.original_price,
            p.discount,
            p.image,
            p.in_stock
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE 1=1
    ";
    
    if ($userId) {
        $query .= " AND c.user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $userId);
    } else {
        $query .= " AND c.session_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $sessionId);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $items = [];
    $total = 0;
    $discount = 0;
    
    while ($row = $result->fetch_assoc()) {
        $itemTotal = $row['price'] * $row['quantity'];
        $itemDiscount = ($row['original_price'] - $row['price']) * $row['quantity'];
        $total += $itemTotal;
        $discount += $itemDiscount;
        
        $items[] = [
            'id' => $row['id'],
            'product_id' => $row['product_id'],
            'name' => $row['name'],
            'price' => floatval($row['price']),
            'original_price' => floatval($row['original_price']),
            'discount' => intval($row['discount']),
            'quantity' => intval($row['quantity']),
            'image' => $row['image'],
            'in_stock' => (bool)$row['in_stock'],
            'subtotal' => $itemTotal
        ];
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'items' => $items,
            'total' => $total,
            'discount' => $discount,
            'final_total' => $total
        ]
    ]);
    
    $stmt->close();
}

/**
 * Add item to cart
 */
function handleAddToCart($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['product_id']) || !isset($data['quantity'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Product ID and quantity are required'
        ]);
        return;
    }
    
    $productId = intval($data['product_id']);
    $quantity = intval($data['quantity']);
    $userId = isset($data['user_id']) ? intval($data['user_id']) : null;
    $sessionId = getSessionId();
    
    // Check if product exists
    $stmt = $conn->prepare("SELECT id, in_stock FROM products WHERE id = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Product not found'
        ]);
        $stmt->close();
        return;
    }
    
    $product = $result->fetch_assoc();
    if (!$product['in_stock']) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Product is out of stock'
        ]);
        $stmt->close();
        return;
    }
    
    // Check if item already in cart
    if ($userId) {
        $checkStmt = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
        $checkStmt->bind_param("ii", $userId, $productId);
    } else {
        $checkStmt = $conn->prepare("SELECT id, quantity FROM cart WHERE session_id = ? AND product_id = ?");
        $checkStmt->bind_param("si", $sessionId, $productId);
    }
    
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        // Update quantity
        $existing = $checkResult->fetch_assoc();
        $newQuantity = $existing['quantity'] + $quantity;
        
        $updateStmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        $updateStmt->bind_param("ii", $newQuantity, $existing['id']);
        $updateStmt->execute();
        $updateStmt->close();
        
        echo json_encode([
            'success' => true,
            'message' => 'Cart updated successfully'
        ]);
    } else {
        // Insert new item
        $insertStmt = $conn->prepare("INSERT INTO cart (user_id, session_id, product_id, quantity) VALUES (?, ?, ?, ?)");
        $insertStmt->bind_param("isii", $userId, $sessionId, $productId, $quantity);
        $insertStmt->execute();
        $insertStmt->close();
        
        echo json_encode([
            'success' => true,
            'message' => 'Item added to cart successfully'
        ]);
    }
    
    $checkStmt->close();
    $stmt->close();
}

/**
 * Update cart item
 */
function handleUpdateCart($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['cart_id']) || !isset($data['quantity'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Cart ID and quantity are required'
        ]);
        return;
    }
    
    $cartId = intval($data['cart_id']);
    $quantity = intval($data['quantity']);
    
    if ($quantity <= 0) {
        // Remove item
        $stmt = $conn->prepare("DELETE FROM cart WHERE id = ?");
        $stmt->bind_param("i", $cartId);
        $stmt->execute();
        $stmt->close();
        
        echo json_encode([
            'success' => true,
            'message' => 'Item removed from cart'
        ]);
        return;
    }
    
    $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
    $stmt->bind_param("ii", $quantity, $cartId);
    $stmt->execute();
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'message' => 'Cart updated successfully'
    ]);
}

/**
 * Remove item from cart
 */
function handleRemoveFromCart($conn) {
    $cartId = isset($_GET['id']) ? intval($_GET['id']) : null;
    
    if (!$cartId) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Cart ID is required'
        ]);
        return;
    }
    
    $stmt = $conn->prepare("DELETE FROM cart WHERE id = ?");
    $stmt->bind_param("i", $cartId);
    $stmt->execute();
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'message' => 'Item removed from cart'
    ]);
}
?>


