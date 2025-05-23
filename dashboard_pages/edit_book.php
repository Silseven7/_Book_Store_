<?php
require_once __DIR__ . '/../database.php';

if (!isset($_GET['id'])) {
    header('Location: /_Book_Store_/books');
    exit;
}

$book_id = $_GET['id'];

// Get book details
$query = "SELECT * FROM books WHERE id = :id";
$stmt = $pdo->prepare($query);
$stmt->execute([':id' => $book_id]);
$book = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$book) {
    header('Location: /_Book_Store_/books');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $author = $_POST['author'] ?? '';
    $isbn = $_POST['isbn'] ?? '';
    $description = $_POST['description'] ?? '';
    $publication_date = $_POST['publication_date'] ?? '';
    $cover_image = $_FILES['cover_image'] ?? null;

    $errors = [];

    // Validate input
    if (empty($title)) $errors[] = "Title is required";
    if (empty($author)) $errors[] = "Author is required";
    if (empty($isbn)) $errors[] = "ISBN is required";
    if (empty($description)) $errors[] = "Description is required";
    if (empty($publication_date)) $errors[] = "Publication date is required";

    // Handle cover image upload
    $cover_image_path = $book['cover_image']; // Keep existing image by default
    if ($cover_image && $cover_image['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($cover_image['type'], $allowed_types)) {
            $errors[] = "Invalid image type. Please upload JPG, PNG, or GIF.";
        } else {
            $upload_dir = __DIR__ . '/../uploads/covers/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $filename = uniqid() . '_' . basename($cover_image['name']);
            $target_path = $upload_dir . $filename;
            
            if (move_uploaded_file($cover_image['tmp_name'], $target_path)) {
                // Delete old image if it exists
                if ($book['cover_image'] && file_exists(__DIR__ . '/../' . $book['cover_image'])) {
                    unlink(__DIR__ . '/../' . $book['cover_image']);
                }
                $cover_image_path = 'uploads/covers/' . $filename;
            } else {
                $errors[] = "Failed to upload cover image.";
            }
        }
    }

    if (empty($errors)) {
        try {
            $query = "UPDATE books SET 
                     title = :title, 
                     author = :author, 
                     isbn = :isbn, 
                     description = :description, 
                     publication_date = :publication_date, 
                     cover_image = :cover_image 
                     WHERE id = :id";
            
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                ':title' => $title,
                ':author' => $author,
                ':isbn' => $isbn,
                ':description' => $description,
                ':publication_date' => $publication_date,
                ':cover_image' => $cover_image_path,
                ':id' => $book_id
            ]);

            header('Location: /_Book_Store_/book_details?id=' . $book_id);
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Duplicate entry
                $errors[] = "A book with this ISBN already exists.";
            } else {
                $errors[] = "Database error: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Book - Book Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include __DIR__ . '/../auth/header.php'; ?>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Edit Book</h4>
                        <a href="/_Book_Store_/book_details?id=<?php echo $book_id; ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Book
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>

                        <form action="" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="title" class="form-label">Title</label>
                                <input type="text" class="form-control" id="title" name="title" 
                                       value="<?php echo htmlspecialchars($book['title']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="author" class="form-label">Author</label>
                                <input type="text" class="form-control" id="author" name="author" 
                                       value="<?php echo htmlspecialchars($book['author']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="isbn" class="form-label">ISBN</label>
                                <input type="text" class="form-control" id="isbn" name="isbn" 
                                       value="<?php echo htmlspecialchars($book['isbn']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="4" required><?php 
                                    echo htmlspecialchars($book['description']); 
                                ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="publication_date" class="form-label">Publication Date</label>
                                <input type="date" class="form-control" id="publication_date" name="publication_date" 
                                       value="<?php echo htmlspecialchars($book['publication_date']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="cover_image" class="form-label">Cover Image</label>
                                <?php if ($book['cover_image']): ?>
                                <div class="mb-2">
                                    <img src="/_Book_Store_/<?php echo htmlspecialchars($book['cover_image']); ?>" 
                                         alt="Current cover" class="img-thumbnail" style="max-height: 200px;">
                                </div>
                                <?php endif; ?>
                                <input type="file" class="form-control" id="cover_image" name="cover_image" 
                                       accept="image/jpeg,image/png,image/gif">
                                <div class="form-text">Upload a new image to replace the current cover (JPG, PNG, or GIF)</div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 