<?php
require_once __DIR__ . '/../database.php';

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get the book_id from the request body
$data = json_decode(file_get_contents('php://input'), true);
$book_id = $data['book_id'] ?? null;

if (!$book_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Book ID is required']);
    exit;
}

try {
    // Delete the book from the database
    $query = "DELETE FROM books WHERE id = :book_id";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':book_id' => $book_id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => 'Book deleted successfully']);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Book not found']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} 