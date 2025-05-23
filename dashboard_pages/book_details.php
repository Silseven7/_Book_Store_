<?php
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
    <title><?php echo htmlspecialchars($book['title']); ?> - Book Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .book-cover {
            max-width: 300px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .rating-stars {
            color: #ffc107;
        }
        .review-card {
            border-left: 4px solid #007bff;
        }
        .back-button {
            margin-bottom: 20px;
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
                    <div class="btn-group w-100">
                        <button class="btn btn-outline-primary" onclick="updateReadingStatus('want_to_read')">
                            <i class="fas fa-bookmark"></i> Want to Read
                        </button>
                        <button class="btn btn-outline-primary" onclick="updateReadingStatus('currently_reading')">
                            <i class="fas fa-book-open"></i> Currently Reading
                        </button>
                        <button class="btn btn-outline-primary" onclick="updateReadingStatus('read')">
                            <i class="fas fa-check"></i> Read
                        </button>
                    </div>
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
                    <div class="card review-card mb-3">
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

        // Reading status update
        async function updateReadingStatus(status) {
            try {
                const response = await fetch('/_Book_Store_/api/update_reading_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        book_id: <?php echo $book_id; ?>,
                        status: status
                    })
                });
                
                if (response.ok) {
                    location.reload();
                } else {
                    alert('Error updating reading status. Please try again.');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error updating reading status. Please try again.');
            }
        }
    </script>
</body>
</html> 