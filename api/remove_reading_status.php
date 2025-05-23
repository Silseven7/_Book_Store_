<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../database.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please log in to remove books from reading list']);
    exit;
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get data from request
$data = json_decode(file_get_contents('php://input'), true);
$book_id = $data['book_id'] ?? null;

if (!$book_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Book ID is required']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Delete from reading list
    $query = "DELETE FROM reading_lists WHERE user_id = :user_id AND book_id = :book_id";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        ':user_id' => $user_id,
        ':book_id' => $book_id
    ]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Book removed from reading list']);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Book not found in reading list']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error removing book from reading list']);
}
?> 