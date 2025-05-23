<?php
session_start();
require_once '../database.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get JSON data
$data = json_decode(file_get_contents('php://input'), true);
$user_id = $_SESSION['user_id'];
$book_id = $data['book_id'] ?? null;
$status = $data['status'] ?? null;

if (!$book_id || !$status) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

if (!in_array($status, ['want_to_read', 'currently_reading', 'read'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid status']);
    exit;
}

try {
    // Start transaction
    $pdo->beginTransaction();

    // Update reading status
    $query = "INSERT INTO reading_lists (user_id, book_id, status, date_added) 
              VALUES (:user_id, :book_id, :status, CURRENT_TIMESTAMP)
              ON DUPLICATE KEY UPDATE 
              status = :status,
              date_added = IF(:status = 'want_to_read', date_added, CURRENT_TIMESTAMP),
              date_started = IF(:status = 'currently_reading', 
                              IF(date_started IS NULL, CURRENT_DATE, date_started),
                              date_started),
              date_finished = IF(:status = 'read', 
                               IF(date_finished IS NULL, CURRENT_DATE, date_finished),
                               date_finished)";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        ':user_id' => $user_id,
        ':book_id' => $book_id,
        ':status' => $status
    ]);

    $pdo->commit();
    http_response_code(200);
    echo json_encode(['message' => 'Reading status updated successfully']);
} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?> 