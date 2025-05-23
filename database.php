<?php
//below configiration belongs to database in db4free.net
//from https://www.db4free.net/phpMyAdmin/ you can login,
//with username + password(same as below) and view database + table content and make queries

$host = "db4free.net";
$dbname = "book_db_w";
$db_username = "book_store__";
$db_password = "bookphp123";
$port = 3306;

$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $db_username, $db_password);
    
    // Set error mode to exception for better debugging
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // echo "Connected successfully!"; //uncomment when trying to test connection
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

function get_username($pdo, $username){
    $query = "SELECT username FROM users WHERE username = :username;";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":username",$username);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result;
}

function get_email($pdo, $email){
    $query = "SELECT email FROM users WHERE email = :email;";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":email", $email);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result;
}

function get_user_info($pdo, $username){
    $query = "SELECT * FROM users WHERE username = :username";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    return $user;
}

// Create necessary tables if they don't exist
function create_tables($pdo) {
    // Books table
    $pdo->exec("CREATE TABLE IF NOT EXISTS books (
        id INT AUTO_INCREMENT PRIMARY KEY,
        isbn VARCHAR(13) UNIQUE,
        title VARCHAR(255) NOT NULL,
        author VARCHAR(255) NOT NULL,
        cover_image VARCHAR(255),
        description TEXT,
        publication_date DATE,
        average_rating DECIMAL(3,2) DEFAULT 0,
        total_ratings INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Reviews table
    $pdo->exec("CREATE TABLE IF NOT EXISTS reviews (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        book_id INT NOT NULL,
        rating INT CHECK (rating >= 1 AND rating <= 5),
        review_text TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (book_id) REFERENCES books(id),
        UNIQUE KEY unique_review (user_id, book_id)
    )");

    // Reading Lists table
    $pdo->exec("CREATE TABLE IF NOT EXISTS reading_lists (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        book_id INT NOT NULL,
        status ENUM('want_to_read', 'currently_reading', 'read') NOT NULL,
        date_added TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        date_started DATE,
        date_finished DATE,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (book_id) REFERENCES books(id),
        UNIQUE KEY unique_reading_status (user_id, book_id)
    )");

    // User Profiles table
    $pdo->exec("CREATE TABLE IF NOT EXISTS user_profiles (
        user_id INT PRIMARY KEY,
        bio TEXT,
        location VARCHAR(255),
        website VARCHAR(255),
        reading_goal INT,
        profile_image VARCHAR(255),
        FOREIGN KEY (user_id) REFERENCES users(id)
    )");

    // User Follows table
    $pdo->exec("CREATE TABLE IF NOT EXISTS user_follows (
        follower_id INT NOT NULL,
        following_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (follower_id, following_id),
        FOREIGN KEY (follower_id) REFERENCES users(id),
        FOREIGN KEY (following_id) REFERENCES users(id)
    )");

    // Book Shelves table
    $pdo->exec("CREATE TABLE IF NOT EXISTS book_shelves (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        is_public BOOLEAN DEFAULT true,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )");

    // Shelf Books table
    $pdo->exec("CREATE TABLE IF NOT EXISTS shelf_books (
        shelf_id INT NOT NULL,
        book_id INT NOT NULL,
        added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (shelf_id, book_id),
        FOREIGN KEY (shelf_id) REFERENCES book_shelves(id),
        FOREIGN KEY (book_id) REFERENCES books(id)
    )");
}

// Function to get book details
function get_book_details($pdo, $book_id) {
    $query = "SELECT * FROM books WHERE id = :book_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':book_id', $book_id);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Function to get user's reading list
function get_user_reading_list($pdo, $user_id, $status = null) {
    $query = "SELECT b.*, rl.status, rl.date_added, rl.date_started, rl.date_finished 
              FROM reading_lists rl 
              JOIN books b ON rl.book_id = b.id 
              WHERE rl.user_id = :user_id";
    
    if ($status) {
        $query .= " AND rl.status = :status";
    }
    
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    
    if ($status) {
        $stmt->bindParam(':status', $status);
    }
    
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get book reviews
function get_book_reviews($pdo, $book_id, $limit = 10, $offset = 0) {
    $query = "SELECT r.*, u.username, up.profile_image 
              FROM reviews r 
              JOIN users u ON r.user_id = u.id 
              LEFT JOIN user_profiles up ON u.id = up.user_id 
              WHERE r.book_id = :book_id 
              ORDER BY r.created_at DESC 
              LIMIT :limit OFFSET :offset";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':book_id', $book_id);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to search books
function search_books($pdo, $search_term, $limit = 20, $offset = 0) {
    $search_term = "%$search_term%";
    $query = "SELECT * FROM books 
              WHERE title LIKE :search_term 
              OR author LIKE :search_term 
              OR isbn LIKE :search_term 
              LIMIT :limit OFFSET :offset";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':search_term', $search_term);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Initialize tables
create_tables($pdo);
?>