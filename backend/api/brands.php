<?php
/**
 * Brands API Endpoint
 */

require_once '../config/database.php';
require_once '../config/cors.php';
require_once '../config/error_handler.php';

$conn = getDBConnection();

try {
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method === 'GET') {
        $stmt = $conn->query("SELECT * FROM brands ORDER BY name ASC");
        $brands = [];
        
        while ($row = $stmt->fetch_assoc()) {
            $brands[] = $row;
        }
        
        echo json_encode([
            'success' => true,
            'data' => $brands
        ]);
        
        $stmt->close();
    } elseif ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['name'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Brand name is required'
            ]);
            exit;
        }
        
        $name = $data['name'];
        $logoUrl = isset($data['logo_url']) ? $data['logo_url'] : '';
        
        $stmt = $conn->prepare("INSERT INTO brands (name, logo_url) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $logoUrl);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Brand created successfully',
                'data' => ['id' => $conn->insert_id]
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Failed to create brand',
                'error' => $conn->error
            ]);
        }
        
        $stmt->close();
    } elseif ($method === 'PUT') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['id'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Brand ID is required'
            ]);
            exit;
        }
        
        $id = intval($data['id']);
        $name = $data['name'] ?? '';
        $logoUrl = $data['logo_url'] ?? '';
        
        $stmt = $conn->prepare("UPDATE brands SET name = ?, logo_url = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $logoUrl, $id);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Brand updated successfully'
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Failed to update brand',
                'error' => $conn->error
            ]);
        }
        
        $stmt->close();
    } elseif ($method === 'DELETE') {
        $id = isset($_GET['id']) ? intval($_GET['id']) : null;
        
        if (!$id) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Brand ID is required'
            ]);
            exit;
        }
        
        $stmt = $conn->prepare("DELETE FROM brands WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Brand deleted successfully'
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Failed to delete brand',
                'error' => $conn->error
            ]);
        }
        
        $stmt->close();
    } else {
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'message' => 'Method not allowed'
        ]);
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
?>

