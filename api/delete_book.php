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
    // Start a transaction
    $pdo->beginTransaction();

    // Delete from dependent tables
    $deleteReadingLists = $pdo->prepare("DELETE FROM reading_lists WHERE book_id = :book_id");
    $deleteReadingLists->execute([':book_id' => $book_id]);

    $deleteUserLibrary = $pdo->prepare("DELETE FROM user_library WHERE book_id = :book_id");
    $deleteUserLibrary->execute([':book_id' => $book_id]);

    // Then delete the book
    $deleteBook = $pdo->prepare("DELETE FROM books WHERE id = :book_id");
    $deleteBook->execute([':book_id' => $book_id]);

    if ($deleteBook->rowCount() > 0) {
        $pdo->commit();
        echo json_encode(['success' => 'Book and all related entries deleted successfully']);
    } else {
        $pdo->rollBack();
        http_response_code(404);
        echo json_encode(['error' => 'Book not found']);
    }
} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}