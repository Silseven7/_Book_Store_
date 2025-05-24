<?php
require_once __DIR__ . '/../database.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /_Book_Store_/landing_page');
    exit;
}

$user_id = $_SESSION['user_id'];

// Get user's library books
$query = "SELECT b.*, ul.date_added 
          FROM user_library ul 
          JOIN books b ON ul.book_id = b.id 
          WHERE ul.user_id = :user_id 
          ORDER BY ul.date_added DESC";
$stmt = $pdo->prepare($query);
$stmt->execute([':user_id' => $user_id]);
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Library - Book Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .book-card {
            transition: transform 0.2s;
            height: 100%;
        }
        .book-card:hover {
            transform: translateY(-5px);
        }
        .book-cover {
            height: 300px;
            object-fit: cover;
        }
        .rating-stars {
            color: #ffc107;
        }
        .remove-btn {
            opacity: 0.7;
            transition: opacity 0.2s;
        }
        .remove-btn:hover {
            opacity: 1;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../auth/header.php'; ?>

    <div class="container mt-4">
        <!-- Back Button -->
        <a href="/_Book_Store_/dashboard" class="btn btn-secondary mb-4">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>

        <h2 class="mb-4">Your Library</h2>

        <?php if (empty($books)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Your library is empty. Start adding books to your collection!
            </div>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php foreach ($books as $book): ?>
                    <div class="col">
                        <div class="card h-100" data-book-id="<?php echo $book['id']; ?>">
                            <img src="<?php echo htmlspecialchars($book['cover_image'] ?? 'https://via.placeholder.com/300x450'); ?>" 
                                 class="card-img-top" alt="<?php echo htmlspecialchars($book['title']); ?>"
                                 style="height: 300px; object-fit: cover;">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($book['title']); ?></h5>
                                <p class="card-text text-muted">by <?php echo htmlspecialchars($book['author']); ?></p>
                                <div class="rating-stars mb-2">
                                    <?php
                                    $rating = $book['average_rating'];
                                    for ($i = 1; $i <= 5; $i++) {
                                        if ($i <= $rating) {
                                            echo '<i class="fas fa-star"></i>';
                                        } elseif ($i - 0.5 <= $rating) {
                                            echo '<i class="fas fa-star-half-alt"></i>';
                                        } else {
                                            echo '<i class="far fa-star"></i>';
                                        }
                                    }
                                    ?>
                                    <span class="ms-2"><?php echo number_format($rating, 1); ?></span>
                                </div>
                                <p class="card-text"><?php echo nl2br(htmlspecialchars(substr($book['description'], 0, 150))); ?>...</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <a href="/_Book_Store_/book_details?id=<?php echo $book['id']; ?>" class="btn btn-primary">
                                        <i class="fas fa-book"></i> View Details
                                    </a>
                                    <button class="btn btn-outline-danger" onclick="confirmRemoveBook(<?php echo $book['id']; ?>, '<?php echo htmlspecialchars(addslashes($book['title'])); ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                                <small class="text-muted d-block mt-2">
                                    Added on <?php echo date('F j, Y', strtotime($book['date_added'])); ?>
                                </small>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Remove Book Confirmation Modal -->
    <div class="modal fade" id="removeBookModal" tabindex="-1" aria-labelledby="removeBookModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="removeBookModalLabel">Remove Book</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-dark mb-0">Are you sure you want to remove "<span id="bookTitle" class="fw-bold"></span>" from your library?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmRemoveBtn">Remove</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let bookToRemove = null;
        const removeBookModal = new bootstrap.Modal(document.getElementById('removeBookModal'));

        function confirmRemoveBook(bookId, bookTitle) {
            bookToRemove = bookId;
            document.getElementById('bookTitle').textContent = bookTitle;
            removeBookModal.show();
        }

        document.getElementById('confirmRemoveBtn').addEventListener('click', function() {
            if (bookToRemove) {
                removeFromLibrary(bookToRemove);
                removeBookModal.hide();
            }
        });

        function removeFromLibrary(bookId) {
            fetch('/_Book_Store_/api/remove_book.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    book_id: bookId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove the book card from the UI
                    const bookCard = document.querySelector(`[data-book-id="${bookId}"]`);
                    if (bookCard) {
                        bookCard.remove();
                    }
                    // If no books left, show the empty message
                    if (document.querySelectorAll('.card').length === 0) {
                        location.reload();
                    }
                    // Trigger dashboard update if needed
                    if (data.updateDashboard) {
                        // Dispatch custom event to update dashboard
                        window.dispatchEvent(new CustomEvent('bookRemoved'));
                    }
                } else {
                    // Show error message
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'alert alert-danger mt-3';
                    errorDiv.textContent = data.message;
                    document.querySelector('.container').insertBefore(errorDiv, document.querySelector('.row'));
                    setTimeout(() => errorDiv.remove(), 3000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                const errorDiv = document.createElement('div');
                errorDiv.className = 'alert alert-danger mt-3';
                errorDiv.textContent = 'Error removing book from library';
                document.querySelector('.container').insertBefore(errorDiv, document.querySelector('.row'));
                setTimeout(() => errorDiv.remove(), 3000);
            });
        }
    </script>
</body>
</html>
