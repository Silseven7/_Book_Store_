<?php
require_once __DIR__ . '/database.php';

try {
    // Find all (user_id, book_id) pairs in reading_lists not in user_library
    $query = "SELECT rl.user_id, rl.book_id
              FROM reading_lists rl
              LEFT JOIN user_library ul
              ON rl.user_id = ul.user_id AND rl.book_id = ul.book_id
              WHERE ul.id IS NULL";
    $stmt = $pdo->query($query);
    $missing = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $added = 0;
    foreach ($missing as $row) {
        $insert = $pdo->prepare("INSERT INTO user_library (user_id, book_id, date_added) VALUES (:user_id, :book_id, NOW())");
        $insert->execute([
            ':user_id' => $row['user_id'],
            ':book_id' => $row['book_id']
        ]);
        $added++;
    }

    echo "Sync complete! $added book(s) added to user_library.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 