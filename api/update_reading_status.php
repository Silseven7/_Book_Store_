<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../database.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log session data
error_log("Session data: " . print_r($_SESSION, true));

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    error_log("User not logged in. Session data: " . print_r($_SESSION, true));
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please log in to save books']);
    exit;
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get data from request
$raw_data = file_get_contents('php://input');
error_log("Raw request data: " . $raw_data);
$data = json_decode($raw_data, true);
$book_id = $data['book_id'] ?? null;
$status = $data['status'] ?? null;

if (!$book_id || !$status) {
    error_log("Missing required parameters. Book ID: $book_id, Status: $status");
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Book ID and status are required']);
    exit;
}

// Validate status
$allowed_statuses = ['want_to_read', 'currently_reading', 'read'];
if (!in_array($status, $allowed_statuses)) {
    error_log("Invalid status: $status");
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

$user_id = $_SESSION['user_id'];
error_log("Processing request for user_id: $user_id, book_id: $book_id, status: $status");

try {
    // Start transaction
    $pdo->beginTransaction();

    // Check if book exists
    $query = "SELECT id FROM books WHERE id = :book_id";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':book_id' => $book_id]);
    if (!$stmt->fetch()) {
        error_log("Book not found: $book_id");
        $pdo->rollBack();
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Book not found']);
        exit;
    }

    // Check if book is already in library
    $query = "SELECT id FROM user_library WHERE user_id = :user_id AND book_id = :book_id";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        ':user_id' => $user_id,
        ':book_id' => $book_id
    ]);
    $in_library = $stmt->fetch();

    // Add to user's library if not already there
    if (!$in_library) {
        try {
            $query = "INSERT INTO user_library (user_id, book_id, date_added) 
                      VALUES (:user_id, :book_id, NOW())";
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                ':user_id' => $user_id,
                ':book_id' => $book_id
            ]);
            
            // Verify the insert was successful
            if ($stmt->rowCount() === 0) {
                throw new PDOException("Failed to insert into user_library");
            }
            
            error_log("Added book to library for user_id: $user_id, book_id: $book_id");
        } catch (PDOException $e) {
            error_log("Error adding to library: " . $e->getMessage());
            throw $e;
        }
    }

    // Check if book is already in reading list
    $query = "SELECT id FROM reading_lists WHERE user_id = :user_id AND book_id = :book_id";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':user_id' => $user_id, ':book_id' => $book_id]);
    $existing = $stmt->fetch();

    if ($existing) {
        // Update existing record
        $query = "UPDATE reading_lists SET 
                  status = :status,
                  date_started = CASE 
                      WHEN :status = 'currently_reading' AND date_started IS NULL THEN CURDATE()
                      ELSE date_started 
                  END,
                  date_finished = CASE 
                      WHEN :status = 'read' AND date_finished IS NULL THEN CURDATE()
                      ELSE date_finished 
                  END
                  WHERE user_id = :user_id AND book_id = :book_id";
        error_log("Updating existing reading list entry for user_id: $user_id, book_id: $book_id");
    } else {
        // Insert new record
        $query = "INSERT INTO reading_lists (user_id, book_id, status, date_added, 
                  date_started, date_finished) 
                  VALUES (:user_id, :book_id, :status, NOW(),
                  CASE WHEN :status = 'currently_reading' THEN CURDATE() ELSE NULL END,
                  CASE WHEN :status = 'read' THEN CURDATE() ELSE NULL END)";
        error_log("Creating new reading list entry for user_id: $user_id, book_id: $book_id");
    }

    $stmt = $pdo->prepare($query);
    $stmt->execute([
        ':user_id' => $user_id,
        ':book_id' => $book_id,
        ':status' => $status
    ]);

    // Verify the insert/update was successful
    if ($stmt->rowCount() === 0 && !$existing) {
        throw new PDOException("Failed to insert into reading_lists");
    }

    // Commit transaction
    $pdo->commit();
    error_log("Successfully updated reading status for user_id: $user_id, book_id: $book_id, status: $status");

    // Verify the book is in both tables
    $query = "SELECT 
                (SELECT COUNT(*) FROM user_library WHERE user_id = :user_id AND book_id = :book_id) as in_library,
                (SELECT COUNT(*) FROM reading_lists WHERE user_id = :user_id AND book_id = :book_id) as in_reading_list";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':user_id' => $user_id, ':book_id' => $book_id]);
    $verification = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($verification['in_library'] == 0 || $verification['in_reading_list'] == 0) {
        throw new PDOException("Verification failed: Book not found in both tables");
    }

    echo json_encode(['success' => true, 'message' => 'Book added to your library and reading list']);
} catch (PDOException $e) {
    // Rollback transaction on error
    $pdo->rollBack();
    error_log("Database error: " . $e->getMessage());
    error_log("SQL State: " . $e->getCode());
    error_log("Error Info: " . print_r($stmt->errorInfo(), true));
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error saving book: ' . $e->getMessage()]);
}
?>
