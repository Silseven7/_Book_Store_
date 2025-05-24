<?php
session_start();
require_once __DIR__ . '/../database.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to delete a review']);
    exit;
}

// Get the review ID from the request
$data = json_decode(file_get_contents('php://input'), true);
$review_id = $data['review_id'] ?? null;

if (!$review_id) {
    echo json_encode(['success' => false, 'message' => 'Review ID is required']);
    exit;
}

try {
    // First, verify that the review belongs to the current user
    $stmt = $pdo->prepare("SELECT user_id FROM reviews WHERE id = :review_id");
    $stmt->execute([':review_id' => $review_id]);
    $review = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$review) {
        echo json_encode(['success' => false, 'message' => 'Review not found']);
        exit;
    }

    if ($review['user_id'] != $_SESSION['user_id']) {
        echo json_encode(['success' => false, 'message' => 'You can only delete your own reviews']);
        exit;
    }

    // Delete the review
    $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = :review_id AND user_id = :user_id");
    $result = $stmt->execute([
        ':review_id' => $review_id,
        ':user_id' => $_SESSION['user_id']
    ]);

    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete review']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
} 