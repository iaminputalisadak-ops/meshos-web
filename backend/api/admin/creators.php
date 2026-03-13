<?php
/**
 * Admin: Creator Partner Management
 * GET: list all creators with membership info
 * POST/PATCH: approve/reject creator, approve/reject membership, set commission rate
 */
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/cors.php';

session_set_cookie_params(['path' => '/', 'httponly' => true, 'samesite' => 'Lax']);
session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['admin_logged_in'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$conn = getDBConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $result = $conn->query("
        SELECT cp.*, u.name, u.email, u.phone, u.created_at as user_created,
               (SELECT COUNT(*) FROM creator_memberships cm WHERE cm.creator_id = cp.id) as total_memberships,
               (SELECT cm2.expires_at FROM creator_memberships cm2 WHERE cm2.creator_id = cp.id AND cm2.admin_approval = 'approved' AND cm2.payment_status = 'paid' ORDER BY cm2.expires_at DESC LIMIT 1) as current_expires_at,
               (SELECT cm3.id FROM creator_memberships cm3 WHERE cm3.creator_id = cp.id AND cm3.admin_approval = 'pending' ORDER BY cm3.created_at DESC LIMIT 1) as pending_membership_id,
               (SELECT cm4.payment_status FROM creator_memberships cm4 WHERE cm4.creator_id = cp.id AND cm4.admin_approval = 'pending' ORDER BY cm4.created_at DESC LIMIT 1) as pending_payment_status
        FROM creator_profiles cp
        JOIN users u ON cp.user_id = u.id
        ORDER BY cp.joined_at DESC
    ");
    $list = [];
    while ($row = $result->fetch_assoc()) {
        if ($row['current_expires_at'] && strtotime($row['current_expires_at']) > time()) {
            $row['membership_status'] = 'active';
            $row['days_left'] = (int) ceil((strtotime($row['current_expires_at']) - time()) / 86400);
        } else {
            $row['membership_status'] = $row['current_expires_at'] ? 'expired' : 'none';
            $row['days_left'] = 0;
        }
        $list[] = $row;
    }
    echo json_encode(['success' => true, 'data' => $list]);
    closeDBConnection($conn);
    exit;
}

if ($method === 'POST' || $method === 'PATCH') {
    $input = json_decode(file_get_contents('php://input'), true) ?: [];
    $action = trim($input['action'] ?? '');

    if ($action === 'approve_creator' || $action === 'reject_creator' || $action === 'suspend_creator') {
        $id = (int) ($input['creator_id'] ?? 0);
        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'creator_id required']);
            exit;
        }
        $status = $action === 'approve_creator' ? 'approved' : ($action === 'reject_creator' ? 'rejected' : 'inactive');
        $approvedAt = $action === 'approve_creator' ? ', approved_at = NOW()' : '';
        $approvalStatus = $action === 'suspend_creator' ? 'approved' : $status;
        $activeStatus = $action === 'suspend_creator' ? 'inactive' : ($action === 'approve_creator' ? 'inactive' : 'inactive');

        if ($action === 'approve_creator') {
            $conn->query("UPDATE creator_profiles SET approval_status = 'approved', approved_at = NOW() WHERE id = $id");
        } elseif ($action === 'reject_creator') {
            $conn->query("UPDATE creator_profiles SET approval_status = 'rejected' WHERE id = $id");
        } else {
            $conn->query("UPDATE creator_profiles SET active_status = 'inactive' WHERE id = $id");
        }

        echo json_encode(['success' => true, 'message' => 'Creator ' . str_replace('_creator', '', $action) . 'd']);
        closeDBConnection($conn);
        exit;
    }

    if ($action === 'approve_membership') {
        $membershipId = (int) ($input['membership_id'] ?? 0);
        if (!$membershipId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'membership_id required']);
            exit;
        }

        $memStmt = $conn->prepare("SELECT id, creator_id, payment_status FROM creator_memberships WHERE id = ?");
        $memStmt->bind_param("i", $membershipId);
        $memStmt->execute();
        $mem = $memStmt->get_result()->fetch_assoc();
        $memStmt->close();

        if (!$mem) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Membership not found']);
            exit;
        }

        $startsAt = date('Y-m-d H:i:s');
        $expiresAt = date('Y-m-d H:i:s', strtotime('+28 days'));

        $updMem = $conn->prepare("UPDATE creator_memberships SET admin_approval = 'approved', starts_at = ?, expires_at = ?, updated_at = NOW() WHERE id = ?");
        $updMem->bind_param("ssi", $startsAt, $expiresAt, $membershipId);
        $updMem->execute();
        $updMem->close();

        $creatorId = (int) $mem['creator_id'];
        $conn->query("UPDATE creator_profiles SET approval_status = 'approved', active_status = 'active', approved_at = COALESCE(approved_at, NOW()) WHERE id = $creatorId");

        echo json_encode([
            'success' => true,
            'message' => 'Membership approved. 28-day active period started.',
            'data' => [
                'starts_at' => $startsAt,
                'expires_at' => $expiresAt
            ]
        ]);
        closeDBConnection($conn);
        exit;
    }

    if ($action === 'reject_membership') {
        $membershipId = (int) ($input['membership_id'] ?? 0);
        if (!$membershipId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'membership_id required']);
            exit;
        }
        $conn->query("UPDATE creator_memberships SET admin_approval = 'rejected', updated_at = NOW() WHERE id = $membershipId");
        echo json_encode(['success' => true, 'message' => 'Membership rejected']);
        closeDBConnection($conn);
        exit;
    }

    if ($action === 'set_commission') {
        $creatorId = (int) ($input['creator_id'] ?? 0);
        $rate = isset($input['commission_rate']) ? (float) $input['commission_rate'] : -1;
        if (!$creatorId || $rate < 0 || $rate > 100) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'creator_id and commission_rate (0-100) required']);
            exit;
        }
        $stmt = $conn->prepare("UPDATE creator_profiles SET commission_rate = ? WHERE id = ?");
        $stmt->bind_param("di", $rate, $creatorId);
        $stmt->execute();
        $stmt->close();
        echo json_encode(['success' => true, 'message' => "Commission rate set to {$rate}%"]);
        closeDBConnection($conn);
        exit;
    }

    if ($action === 'mark_payment') {
        $membershipId = (int) ($input['membership_id'] ?? 0);
        $payRef = trim($input['payment_reference'] ?? '');
        if (!$membershipId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'membership_id required']);
            exit;
        }
        $stmt = $conn->prepare("UPDATE creator_memberships SET payment_status = 'paid', payment_reference = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("si", $payRef, $membershipId);
        $stmt->execute();
        $stmt->close();
        echo json_encode(['success' => true, 'message' => 'Payment marked as received']);
        closeDBConnection($conn);
        exit;
    }

    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Unknown action. Valid: approve_creator, reject_creator, suspend_creator, approve_membership, reject_membership, set_commission, mark_payment']);
    closeDBConnection($conn);
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Method not allowed']);
closeDBConnection($conn);
