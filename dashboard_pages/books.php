<?php
require_once __DIR__ . '/../database.php';

// Get search parameters
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'title';
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 12;

// Get books with pagination
$offset = ($page - 1) * $per_page;
$books = search_books($pdo, $search, $per_page, $offset);

// Get total count for pagination
$count_query = "SELECT COUNT(*) FROM books WHERE title LIKE :search OR author LIKE :search OR isbn LIKE :search";
$stmt = $pdo->prepare($count_query);
$stmt->execute([':search' => "%$search%"]);
$total_books = $stmt->fetchColumn();
$total_pages = ceil($total_books / $per_page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Books - Book Store</title>
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
        .delete-btn {
            opacity: 0.7;
            transition: opacity 0.2s;
        }
        .delete-btn:hover {
            opacity: 1;
        }
        .modal-body {
            color: #000;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../auth/header.php'; ?>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Book</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this book?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-4">
        <!-- Back Button -->
        <a href="/_Book_Store_/dashboard" class="btn btn-secondary mb-4">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>

        <!-- Search and Filter Section -->
        <div class="row mb-4">
            <div class="col-md-8">
                <form action="" method="GET" class="d-flex gap-2">
                    <input type="text" name="search" class="form-control" 
                           placeholder="Search by title, author, or ISBN" 
                           value="<?php echo htmlspecialchars($search); ?>">
                    <select name="sort" class="form-select" style="width: auto;">
                        <option value="title" <?php echo $sort === 'title' ? 'selected' : ''; ?>>Title</option>
                        <option value="author" <?php echo $sort === 'author' ? 'selected' : ''; ?>>Author</option>
                        <option value="rating" <?php echo $sort === 'rating' ? 'selected' : ''; ?>>Rating</option>
                        <option value="date" <?php echo $sort === 'date' ? 'selected' : ''; ?>>Publication Date</option>
                    </select>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Search
                    </button>
                </form>
            </div>
            <div class="col-md-4 text-end">
                <a href="/_Book_Store_/add_book" class="btn btn-success">
                    <i class="fas fa-plus"></i> Add New Book
                </a>
            </div>
        </div>

        <!-- Books Grid -->
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4">
            <?php foreach ($books as $book): ?>
            <div class="col">
                <div class="card book-card h-100">
                    <img src="<?php echo htmlspecialchars($book['cover_image'] ?? 'https://via.placeholder.com/300x450'); ?>" 
                         class="card-img-top book-cover" 
                         alt="<?php echo htmlspecialchars($book['title']); ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($book['title']); ?></h5>
                        <h6 class="card-subtitle mb-2 text-muted">
                            by <?php echo htmlspecialchars($book['author']); ?>
                        </h6>
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
                            <small class="text-muted">
                                (<?php echo $book['total_ratings']; ?>)
                            </small>
                        </div>
                        <p class="card-text small text-truncate">
                            <?php echo htmlspecialchars($book['description']); ?>
                        </p>
                    </div>
                    <div class="card-footer bg-transparent">
                        <a href="/_Book_Store_/book_details?id=<?php echo $book['id']; ?>" 
                           class="btn btn-primary btn-sm w-100">
                            View Details
                        </a>
                        <button class="btn btn-danger btn-sm w-100 mt-2 delete-btn" 
                                onclick="showDeleteModal(<?php echo $book['id']; ?>, '<?php echo htmlspecialchars($book['title']); ?>')">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <nav class="mt-4">
            <ul class="pagination justify-content-center">
                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo $sort; ?>">
                        Previous
                    </a>
                </li>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo $sort; ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
                <?php endfor; ?>
                <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo $sort; ?>">
                        Next
                    </a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentBookId = null;
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));

        function showDeleteModal(bookId, bookTitle) {
            currentBookId = bookId;
            document.querySelector('#deleteModal .modal-body').textContent = 
                `Are you sure you want to delete "${bookTitle}"?`;
            deleteModal.show();
        }

        document.getElementById('confirmDelete').addEventListener('click', async function() {
            if (!currentBookId) return;

            try {
                const response = await fetch('/_Book_Store_/api/delete_book.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ book_id: currentBookId })
                });
                
                if (response.ok) {
                    location.reload();
                } else {
                    alert('Error deleting book. Please try again.');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error deleting book. Please try again.');
            }

            deleteModal.hide();
        });
    </script>
</body>
</html>
