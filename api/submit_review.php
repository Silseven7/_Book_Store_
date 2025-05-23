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

$user_id = $_SESSION['user_id'];
$book_id = $_POST['book_id'] ?? null;
$rating = $_POST['rating'] ?? null;
$review_text = $_POST['review_text'] ?? null;

if (!$book_id || !$rating || !$review_text) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

try {
    // Start transaction
    $pdo->beginTransaction();

    // Insert or update review
    $query = "INSERT INTO reviews (user_id, book_id, rating, review_text) 
              VALUES (:user_id, :book_id, :rating, :review_text)
              ON DUPLICATE KEY UPDATE 
              rating = :rating,
              review_text = :review_text,
              created_at = CURRENT_TIMESTAMP";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        ':user_id' => $user_id,
        ':book_id' => $book_id,
        ':rating' => $rating,
        ':review_text' => $review_text
    ]);

    // Update book's average rating
    $query = "UPDATE books b 
              SET average_rating = (
                  SELECT AVG(rating) 
                  FROM reviews 
                  WHERE book_id = :book_id
              ),
              total_ratings = (
                  SELECT COUNT(*) 
                  FROM reviews 
                  WHERE book_id = :book_id
              )
              WHERE id = :book_id";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([':book_id' => $book_id]);

    $pdo->commit();
    http_response_code(200);
    echo json_encode(['message' => 'Review submitted successfully']);
} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?> 