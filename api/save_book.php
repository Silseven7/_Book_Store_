<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../database.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please log in to save books']);
    exit;
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get book ID from request
$data = json_decode(file_get_contents('php://input'), true);
$book_id = $data['book_id'] ?? null;

if (!$book_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Book ID is required']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Check if book exists
    $query = "SELECT id FROM books WHERE id = :book_id";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':book_id' => $book_id]);
    
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Book not found']);
        exit;
    }

    // Check if book is already in user's library
    $query = "SELECT id FROM user_library WHERE user_id = :user_id AND book_id = :book_id";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        ':user_id' => $user_id,
        ':book_id' => $book_id
    ]);
    
    if ($stmt->fetch()) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Book is already in your library']);
        exit;
    }

    // Add book to user's library
    $query = "INSERT INTO user_library (user_id, book_id, date_added) VALUES (:user_id, :book_id, NOW())";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        ':user_id' => $user_id,
        ':book_id' => $book_id
    ]);

    echo json_encode(['success' => true, 'message' => 'Book added to your library']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error saving book to library']);
} 