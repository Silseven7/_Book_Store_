<?php
require_once __DIR__ . '/database.php';

try {
    // Drop the user_library table if it exists
    $pdo->exec("DROP TABLE IF EXISTS user_library");
    
    // Recreate the user_library table
    $pdo->exec("CREATE TABLE user_library (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        book_id INT NOT NULL,
        date_added TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (book_id) REFERENCES books(id),
        UNIQUE KEY unique_user_book (user_id, book_id)
    )");
    
    echo "Library table recreated successfully!";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
