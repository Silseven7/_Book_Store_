<?php
ob_start(); // Start output buffering

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../database.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Helper function to send JSON response and exit
function send_json_response($data, $http_code = 200) {
    ob_clean();
    header('Content-Type: application/json; charset=utf-8');
    http_response_code($http_code);
    echo json_encode($data);
    exit;
}

// Log session data
error_log("Session data: " . print_r($_SESSION, true));

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    error_log("User not logged in. Session data: " . print_r($_SESSION, true));
    send_json_response(['success' => false, 'message' => 'Please log in to save books'], 401);
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    send_json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

// Get data from request
$raw_data = file_get_contents('php://input');
error_log("Raw request data: " . $raw_data);
$data = json_decode($raw_data, true);
$book_id = isset($data['book_id']) ? (int)$data['book_id'] : null;
$status = $data['status'] ?? null;

if (!$book_id || !$status) {
    error_log("Missing required parameters. Book ID: $book_id, Status: $status");
    send_json_response(['success' => false, 'message' => 'Book ID and status are required'], 400);
}

// Validate status
$allowed_statuses = ['want_to_read', 'currently_reading', 'read'];
if (!in_array($status, $allowed_statuses, true)) {
    error_log("Invalid status: $status");
    send_json_response(['success' => false, 'message' => 'Invalid status'], 400);
}

$user_id = $_SESSION['user_id'];
error_log("Processing request for user_id: $user_id, book_id: $book_id, status: $status");

try {
    $pdo->beginTransaction();

    // Check if book exists
    $query = "SELECT id FROM books WHERE id = :book_id";
    $stmt = $pdo->prepare($query);
    if (!$stmt) {
        error_log("❌ Prepare failed: $query");
        error_log("❌ Error: " . print_r($pdo->errorInfo(), true));
        throw new Exception('Database prepare failed.');
    }
    $stmt->execute([':book_id' => $book_id]);

    if (!$stmt->fetch()) {
        error_log("Book not found: $book_id");
        $pdo->rollBack();
        send_json_response(['success' => false, 'message' => 'Book not found'], 404);
    }

    // Check if book already in library
    $query = "SELECT id FROM user_library WHERE user_id = :user_id AND book_id = :book_id";
    $stmt = $pdo->prepare($query);
    if (!$stmt) {
        error_log("❌ Prepare failed (user_library check): $query");
        error_log("❌ Error: " . print_r($pdo->errorInfo(), true));
        throw new Exception('Database prepare failed.');
    }
    $stmt->execute([':user_id' => $user_id, ':book_id' => $book_id]);
    $in_library = $stmt->fetch();

    if (!$in_library) {
        $query = "INSERT INTO user_library (user_id, book_id, date_added) VALUES (:user_id, :book_id, NOW())";
        $stmt = $pdo->prepare($query);
        if (!$stmt) {
            error_log("❌ Prepare failed (user_library insert): $query");
            error_log("❌ Error: " . print_r($pdo->errorInfo(), true));
            throw new Exception('Database prepare failed.');
        }
        if (!$stmt->execute([':user_id' => $user_id, ':book_id' => $book_id])) {
            error_log("❌ Execute failed (user_library insert): $query");
            error_log("❌ Params: " . print_r([':user_id' => $user_id, ':book_id' => $book_id], true));
            error_log("❌ Error Info: " . print_r($stmt->errorInfo(), true));
            throw new PDOException("Failed to insert into user_library");
        }
    }

    // Check if reading list entry already exists
    $query = "SELECT id FROM reading_lists WHERE user_id = :user_id AND book_id = :book_id";
    $stmt = $pdo->prepare($query);
    if (!$stmt) {
        error_log("❌ Prepare failed (check reading_lists): $query");
        error_log("❌ Error: " . print_r($pdo->errorInfo(), true));
        throw new Exception('Database prepare failed.');
    }
    $stmt->execute([':user_id' => $user_id, ':book_id' => $book_id]);
    $existing = $stmt->fetch();

    if ($existing) {
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
        error_log("Updating existing reading list entry");
    } else {
        $query = "INSERT INTO reading_lists (user_id, book_id, status, date_added, 
                  date_started, date_finished) 
                  VALUES (:user_id, :book_id, :status, NOW(),
                  CASE WHEN :status = 'currently_reading' THEN CURDATE() ELSE NULL END,
                  CASE WHEN :status = 'read' THEN CURDATE() ELSE NULL END)";
        error_log("Inserting new reading list entry");
    }

    $stmt = $pdo->prepare($query);
    if (!$stmt) {
        error_log("❌ Prepare failed (reading_lists insert/update): $query");
        error_log("❌ Error: " . print_r($pdo->errorInfo(), true));
        throw new Exception('Database prepare failed.');
    }

    $params = [
        ':user_id' => $user_id,
        ':book_id' => $book_id,
        ':status' => $status
    ];

    if (!$stmt->execute($params)) {
        error_log("❌ Execute failed (reading_lists insert/update): $query");
        error_log("❌ Params: " . print_r($params, true));
        error_log("❌ Error Info: " . print_r($stmt->errorInfo(), true));
        throw new PDOException("Failed to insert/update reading_lists");
    }

    $pdo->commit();
    error_log("✅ Success: user_id=$user_id, book_id=$book_id, status=$status");

    send_json_response(['success' => true, 'message' => 'Book added to your library and reading list']);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log("❌ Exception: " . $e->getMessage());
    error_log("❌ SQL State: " . $e->getCode());
    if (isset($stmt)) {
        error_log("❌ Final Error Info: " . print_r($stmt->errorInfo(), true));
    }

    send_json_response(['success' => false, 'message' => 'Error saving book: ' . $e->getMessage()], 500);
}