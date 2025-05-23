<?php
require_once __DIR__ . '/database.php';

try {
    // Drop the reading_lists table if it exists
    $pdo->exec("DROP TABLE IF EXISTS reading_lists");
    
    // Recreate the reading_lists table
    $pdo->exec("CREATE TABLE reading_lists (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        book_id INT NOT NULL,
        status VARCHAR(20) NOT NULL,
        date_added TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        date_started DATE,
        date_finished DATE,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (book_id) REFERENCES books(id),
        UNIQUE KEY unique_reading_status (user_id, book_id)
    )");
    
    echo "Table recreated successfully!";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 