<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../database.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please log in to manage your library']);
    exit;
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get book ID from request
$raw_data = file_get_contents('php://input');
$data = json_decode($raw_data, true);
$book_id = $data['book_id'] ?? null;

if (!$book_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Book ID is required']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $pdo->beginTransaction();
    // Remove from user_library
    $stmt = $pdo->prepare('DELETE FROM user_library WHERE user_id = :user_id AND book_id = :book_id');
    $stmt->execute([':user_id' => $user_id, ':book_id' => $book_id]);
    // Remove from reading_lists
    $stmt2 = $pdo->prepare('DELETE FROM reading_lists WHERE user_id = :user_id AND book_id = :book_id');
    $stmt2->execute([':user_id' => $user_id, ':book_id' => $book_id]);
    $pdo->commit();
    echo json_encode([
        'success' => true, 
        'message' => 'Book removed from your library and reading list.',
        'updateDashboard' => true
    ]);
} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error removing book: ' . $e->getMessage()]);
} 