<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../database.php';

if (!isset($_GET['id'])) {
    header('Location: /_Book_Store_/books');
    exit;
}

$book_id = $_GET['id'];
$book = get_book_details($pdo, $book_id);

if (!$book) {
    header('Location: /_Book_Store_/books');
    exit;
}

$reviews = get_book_reviews($pdo, $book_id);
$user_id = $_SESSION['user_id'] ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($book['title']); ?> - ShelfShare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            border: none;
            border-radius: 10px;
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;
            color: #2c3e50;
        }

        .btn-secondary:hover {
            background: linear-gradient(135deg, #c3cfe2 0%, #f5f7fa 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .btn-success {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .btn-outline-primary {
            border: 2px solid #667eea;
            color: #667eea;
            background: transparent;
            border-radius: 10px;
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;
        }

        .btn-outline-primary:hover {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-color: transparent;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .btn-outline-primary.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-color: transparent;
            color: white;
        }

        .book-cover {
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .back-button {
            margin-bottom: 2rem;
        }
        .rating-stars {
            color: #ffc107;
        }
        .review-card {
            border-left: 4px solid #007bff;
        }
        .details-overlay {
            background-color: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .book-details {
            color: #000;
        }
        .book-details h1, 
        .book-details h4, 
        .book-details h5, 
        .book-details p, 
        .book-details li {
            color: #000;
        }
        .book-details .text-muted {
            color: #666 !important;
        }
        .book-title {
            color: #8B0000;
            font-weight: bold;
        }
        .section-title {
            color: #8B0000;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../auth/header.php'; ?>

    <div class="container mt-4">
        <a href="/_Book_Store_/books" class="btn btn-secondary back-button">
            <i class="fas fa-arrow-left"></i> Back to Books
        </a>

        <div class="row">
            <!-- Book Cover and Basic Info -->
            <div class="col-md-4">
                <img src="<?php echo htmlspecialchars($book['cover_image'] ?? 'https://via.placeholder.com/300x450'); ?>" 
                     alt="<?php echo htmlspecialchars($book['title']); ?>" 
                     class="book-cover img-fluid rounded">
                
                <?php if ($user_id): ?>
                <div class="mt-3">
                    <?php
                    // Get current reading status
                    $query = "SELECT status FROM reading_lists WHERE user_id = :user_id AND book_id = :book_id";
                    $stmt = $pdo->prepare($query);
                    $stmt->execute([':user_id' => $user_id, ':book_id' => $book_id]);
                    $current_status = $stmt->fetchColumn();

                    // Check if book is already in user's library
                    $query = "SELECT id FROM user_library WHERE user_id = :user_id AND book_id = :book_id";
                    $stmt = $pdo->prepare($query);
                    $stmt->execute([':user_id' => $user_id, ':book_id' => $book_id]);
                    $is_in_library = $stmt->fetch();
                    ?>
                    <div class="btn-group w-100" data-book-id="<?php echo $book_id; ?>">
                        <button class="btn btn-outline-primary <?php echo $current_status === 'want_to_read' ? 'active' : ''; ?>" 
                                data-status="want_to_read"
                                onclick="updateReadingStatus('want_to_read')">
                            <i class="fas fa-bookmark"></i> Want to Read
                        </button>
                        <button class="btn btn-outline-primary <?php echo $current_status === 'currently_reading' ? 'active' : ''; ?>" 
                                data-status="currently_reading"
                                onclick="updateReadingStatus('currently_reading')">
                            <i class="fas fa-book-open"></i> Currently Reading
                        </button>
                        <button class="btn btn-outline-primary <?php echo $current_status === 'read' ? 'active' : ''; ?>" 
                                data-status="read"
                                onclick="updateReadingStatus('read')">
                            <i class="fas fa-check"></i> Read
                        </button>
                    </div>
                    <?php if (!$is_in_library): ?>
                    <button class="btn btn-success w-100 mt-2" onclick="saveToLibrary()">
                        <i class="fas fa-plus"></i> Save to Library
                    </button>
                    <?php else: ?>
                    <button class="btn btn-secondary w-100 mt-2" disabled>
                        <i class="fas fa-check"></i> Saved to Library
                    </button>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Book Details -->
            <div class="col-md-8">
                <div class="details-overlay">
                    <h1 class="book-title"><?php echo htmlspecialchars($book['title']); ?></h1>
                    <h4 class="text-muted">by <?php echo htmlspecialchars($book['author']); ?></h4>
                    
                    <div class="mt-3">
                        <div class="rating-stars">
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
                            <span class="text-muted">(<?php echo $book['total_ratings']; ?> ratings)</span>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h5 class="section-title">Description</h5>
                        <p class="book-details"><?php echo nl2br(htmlspecialchars($book['description'])); ?></p>
                    </div>

                    <div class="mt-4">
                        <h5>Details</h5>
                        <ul class="list-unstyled book-details">
                            <li><strong>ISBN:</strong> <?php echo htmlspecialchars($book['isbn']); ?></li>
                            <li><strong>Published:</strong> <?php echo date('F j, Y', strtotime($book['publication_date'])); ?></li>
                        </ul>
                    </div>

                    <a href="/_Book_Store_/edit_book?id=<?php echo $book['id']; ?>" class="btn btn-primary mt-3">
                        <i class="fas fa-edit"></i> Edit Book
                    </a>
                </div>

                <?php if ($user_id): ?>
                <div class="mt-4">
                    <h5>Write a Review</h5>
                    <form id="reviewForm" class="mb-4">
                        <div class="mb-3">
                            <label class="form-label">Rating</label>
                            <div class="rating-input">
                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                <input type="radio" name="rating" value="<?php echo $i; ?>" id="star<?php echo $i; ?>" required>
                                <label for="star<?php echo $i; ?>"><i class="far fa-star"></i></label>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="reviewText" class="form-label">Review</label>
                            <textarea class="form-control" id="reviewText" name="review_text" rows="4" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit Review</button>
                    </form>
                </div>
                <?php endif; ?>

                <!-- Reviews Section -->
                <div class="mt-4">
                    <h5>Reviews</h5>
                    <?php foreach ($reviews as $review): ?>
                    <div class="card review-card mb-3" data-review-id="<?php echo $review['id']; ?>">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-2">
                                <img src="<?php echo htmlspecialchars($review['profile_image'] ?? 'https://via.placeholder.com/40'); ?>" 
                                     alt="<?php echo htmlspecialchars($review['username']); ?>" 
                                     class="rounded-circle me-2" style="width: 40px; height: 40px;">
                                <div>
                                    <h6 class="mb-0"><?php echo htmlspecialchars($review['username']); ?></h6>
                                    <small class="text-muted">
                                        <?php echo date('F j, Y', strtotime($review['created_at'])); ?>
                                    </small>
                                </div>
                                <?php if ($user_id && $review['user_id'] == $user_id): ?>
                                <div class="ms-auto">
                                    <button onclick="deleteReview(<?php echo $review['id']; ?>)" class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="rating-stars mb-2">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star<?php echo $i <= $review['rating'] ? '' : '-o'; ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <p class="card-text"><?php echo nl2br(htmlspecialchars($review['review_text'])); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Rating input styling
        document.querySelectorAll('.rating-input label').forEach(label => {
            label.addEventListener('mouseover', function() {
                const rating = this.previousElementSibling.value;
                document.querySelectorAll('.rating-input label i').forEach((star, index) => {
                    star.className = index < rating ? 'fas fa-star' : 'far fa-star';
                });
            });
        });

        document.querySelector('.rating-input').addEventListener('mouseleave', function() {
            const selectedRating = document.querySelector('input[name="rating"]:checked');
            document.querySelectorAll('.rating-input label i').forEach((star, index) => {
                star.className = selectedRating && index < selectedRating.value ? 'fas fa-star' : 'far fa-star';
            });
        });

        // Review form submission
        document.getElementById('reviewForm')?.addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('book_id', <?php echo $book_id; ?>);

            try {
                const response = await fetch('/_Book_Store_/api/submit_review.php', {
                    method: 'POST',
                    body: formData
                });
                
                if (response.ok) {
                    location.reload();
                } else {
                    alert('Error submitting review. Please try again.');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error submitting review. Please try again.');
            }
        });

        function saveToLibrary() {
            fetch('/_Book_Store_/api/save_book.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    book_id: <?php echo $book_id; ?>
                })
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(data.message || 'Error saving book to library');
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Update button appearance
                    const saveBtn = document.querySelector('.btn-success');
                    saveBtn.innerHTML = '<i class="fas fa-check"></i> Saved to Library';
                    saveBtn.classList.remove('btn-success');
                    saveBtn.classList.add('btn-secondary');
                    saveBtn.disabled = true;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                const errorDiv = document.createElement('div');
                errorDiv.className = 'alert alert-danger mt-2';
                errorDiv.textContent = error.message;
                document.querySelector('.btn-success').parentNode.appendChild(errorDiv);
                setTimeout(() => errorDiv.remove(), 3000);
            });
        }

        function updateReadingStatus(status) {
            const btnGroup = document.querySelector('.btn-group');
            const buttons = btnGroup.querySelectorAll('.btn');
            
            // First update UI to show immediate feedback
            buttons.forEach(btn => {
                btn.classList.remove('active');
                if (btn.dataset.status === status) {
                    btn.classList.add('active');
                }
            });

            // Then send request to server
            fetch('/_Book_Store_/api/update_reading_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    book_id: <?php echo $book_id; ?>,
                    status: status
                })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    // If request failed, revert the UI change
                    buttons.forEach(btn => {
                        btn.classList.remove('active');
                        if (btn.dataset.status === '<?php echo $current_status; ?>') {
                            btn.classList.add('active');
                        }
                    });
                    
                    // Show error message
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'alert alert-danger mt-2';
                    errorDiv.textContent = data.message;
                    btnGroup.parentNode.appendChild(errorDiv);
                    setTimeout(() => errorDiv.remove(), 3000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // If request failed, revert the UI change
                buttons.forEach(btn => {
                    btn.classList.remove('active');
                    if (btn.dataset.status === '<?php echo $current_status; ?>') {
                        btn.classList.add('active');
                    }
                });
                
                const errorDiv = document.createElement('div');
                errorDiv.className = 'alert alert-danger mt-2';
                errorDiv.textContent = 'Error updating reading status';
                btnGroup.parentNode.appendChild(errorDiv);
                setTimeout(() => errorDiv.remove(), 3000);
            });
        }

        function deleteReview(reviewId) {
            const reviewCard = document.querySelector(`[data-review-id="${reviewId}"]`);
            const confirmDiv = document.createElement('div');
            confirmDiv.className = 'alert alert-warning mt-2';
            confirmDiv.innerHTML = `
                Are you sure you want to delete this review?
                <div class="mt-2">
                    <button class="btn btn-danger btn-sm me-2" onclick="confirmDelete(${reviewId})">Yes, Delete</button>
                    <button class="btn btn-secondary btn-sm" onclick="cancelDelete(${reviewId})">Cancel</button>
                </div>
            `;
            reviewCard.appendChild(confirmDiv);
        }

        function confirmDelete(reviewId) {
            fetch('/_Book_Store_/api/delete_review.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    review_id: reviewId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const reviewCard = document.querySelector(`[data-review-id="${reviewId}"]`);
                    reviewCard.remove();
                    // Show success message
                    const successDiv = document.createElement('div');
                    successDiv.className = 'alert alert-success mt-3';
                    successDiv.textContent = 'Review deleted successfully';
                    document.querySelector('.reviews-section').prepend(successDiv);
                    setTimeout(() => successDiv.remove(), 3000);
                } else {
                    // Show error message
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'alert alert-danger mt-3';
                    errorDiv.textContent = data.message || 'Error deleting review';
                    document.querySelector('.reviews-section').prepend(errorDiv);
                    setTimeout(() => errorDiv.remove(), 3000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                const errorDiv = document.createElement('div');
                errorDiv.className = 'alert alert-danger mt-3';
                errorDiv.textContent = 'Error deleting review';
                document.querySelector('.reviews-section').prepend(errorDiv);
                setTimeout(() => errorDiv.remove(), 3000);
            });
        }

        function cancelDelete(reviewId) {
            const reviewCard = document.querySelector(`[data-review-id="${reviewId}"]`);
            const confirmDiv = reviewCard.querySelector('.alert');
            if (confirmDiv) {
                confirmDiv.remove();
            }
        }
    </script>
</body>
</html> 