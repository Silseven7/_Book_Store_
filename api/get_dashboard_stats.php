<?php
require_once __DIR__ . '/../database.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Get counts for each status
$counts = [
    'want_to_read' => 0,
    'currently_reading' => 0,
    'read' => 0
];

$count_query = "SELECT status, COUNT(*) as count 
                FROM reading_lists 
                WHERE user_id = :user_id 
                GROUP BY status";
$stmt = $pdo->prepare($count_query);
$stmt->execute([':user_id' => $user_id]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($results as $result) {
    $counts[$result['status']] = $result['count'];
}

// Get total books in library
$library_query = "SELECT COUNT(*) FROM user_library WHERE user_id = :user_id";
$stmt = $pdo->prepare($library_query);
$stmt->execute([':user_id' => $user_id]);
$total_books = $stmt->fetchColumn();

// Get average rating
$rating_query = "SELECT AVG(rating) FROM reviews WHERE user_id = :user_id";
$stmt = $pdo->prepare($rating_query);
$stmt->execute([':user_id' => $user_id]);
$average_rating = $stmt->fetchColumn() ?: 0;

// Get total reviews
$reviews_query = "SELECT COUNT(*) FROM reviews WHERE user_id = :user_id";
$stmt = $pdo->prepare($reviews_query);
$stmt->execute([':user_id' => $user_id]);
$total_reviews = $stmt->fetchColumn();

// Prepare response
$response = [
    'total_books' => (int)$total_books,
    'want_to_read' => (int)$counts['want_to_read'],
    'currently_reading' => (int)$counts['currently_reading'],
    'books_read' => (int)$counts['read'],
    'average_rating' => (float)$average_rating,
    'total_reviews' => (int)$total_reviews
];

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response); 