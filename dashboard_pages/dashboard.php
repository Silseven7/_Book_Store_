<?php
require_once __DIR__ . '/../database.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /_Book_Store_/landing_page');
    exit;
}

// Get user's reading statistics
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    // If no user_id but logged in, log them out
    session_destroy();
    header('Location: /_Book_Store_/landing_page');
    exit;
}

// Get counts for each status
$counts = [
    'want_to_read' => 0,
    'currently_reading' => 0,
    'read' => 0
];

$count_query = "SELECT status, COUNT(*) as count 
                FROM reading_lists 
                WHERE user_id = :user_id 
                GROUP BY status";
$stmt = $pdo->prepare($count_query);
$stmt->execute([':user_id' => $user_id]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($results as $result) {
    $counts[$result['status']] = $result['count'];
}

// Reading list = all except 'read'
$reading_list_count = $counts['want_to_read'] + $counts['currently_reading'];
// Books read = 'read'
$books_read_count = $counts['read'];

// Get total books in library
$library_query = "SELECT COUNT(*) FROM user_library WHERE user_id = :user_id";
$stmt = $pdo->prepare($library_query);
$stmt->execute([':user_id' => $user_id]);
$total_books = $stmt->fetchColumn();

// Get average rating
$rating_query = "SELECT AVG(rating) FROM reviews WHERE user_id = :user_id";
$stmt = $pdo->prepare($rating_query);
$stmt->execute([':user_id' => $user_id]);
$average_rating = $stmt->fetchColumn() ?: 0;

// Get total reviews
$reviews_query = "SELECT COUNT(*) FROM reviews WHERE user_id = :user_id";
$stmt = $pdo->prepare($reviews_query);
$stmt->execute([':user_id' => $user_id]);
$total_reviews = $stmt->fetchColumn();

$stats = [
    'total_books' => $total_books,
    'average_rating' => $average_rating,
    'total_reviews' => $total_reviews,
    'reading_list' => $reading_list_count,
    'books_read' => $books_read_count
];

// Array of famous writer quotes
$writer_quotes = [
    [
        'quote' => "A reader lives a thousand lives before he dies. The man who never reads lives only one.",
        'author' => "George R.R. Martin",
        'book' => "A Dance with Dragons"
    ],
    [
        'quote' => "Books are a uniquely portable magic.",
        'author' => "Stephen King",
        'book' => "On Writing"
    ],
    [
        'quote' => "I have always imagined that Paradise will be a kind of library.",
        'author' => "Jorge Luis Borges",
        'book' => "Dreamtigers"
    ],
    [
        'quote' => "The more that you read, the more things you will know. The more that you learn, the more places you'll go.",
        'author' => "Dr. Seuss",
        'book' => "I Can Read With My Eyes Shut!"
    ],
    [
        'quote' => "There is no friend as loyal as a book.",
        'author' => "Ernest Hemingway",
        'book' => "A Moveable Feast"
    ],
    [
        'quote' => "Books are the quietest and most constant of friends; they are the most accessible and wisest of counselors, and the most patient of teachers.",
        'author' => "Charles W. Eliot",
        'book' => "The Happy Life"
    ],
    [
        'quote' => "Reading is essential for those who seek to rise above the ordinary.",
        'author' => "Jim Rohn",
        'book' => "The Art of Exceptional Living"
    ],
    [
        'quote' => "The reading of all good books is like conversation with the finest men of past centuries.",
        'author' => "René Descartes",
        'book' => "Discourse on Method"
    ]
];

// Get a random quote
$random_quote = $writer_quotes[array_rand($writer_quotes)];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Book Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .dashboard-card {
            transition: all 0.3s ease;
            height: 100%;
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            background: white;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }

        .quote-card {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .quote-text {
            font-style: italic;
            font-size: 1.2rem;
            color: #2c3e50;
            line-height: 1.6;
        }

        .quote-author {
            color: #34495e;
            font-weight: 500;
        }

        .quote-book {
            color: #7f8c8d;
            font-size: 0.9rem;
        }

        .clock-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            position: relative;
        }

        .clock-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 100%);
            z-index: 1;
        }

        .clock-time {
            font-size: 3rem;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
            position: relative;
            z-index: 2;
        }

        .clock-date {
            font-size: 1.2rem;
            opacity: 0.9;
            position: relative;
            z-index: 2;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: none;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }

        .stat-icon {
            font-size: 2.5rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 1rem;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #7f8c8d;
            font-size: 1rem;
            font-weight: 500;
        }

        .action-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: none;
            overflow: hidden;
            position: relative;
        }

        .action-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }

        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }

        .action-card .card-body {
            padding: 2rem;
            position: relative;
            z-index: 1;
        }

        .action-card .card-title {
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .action-card .card-title i {
            font-size: 1.5rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .action-card .card-text {
            color: #7f8c8d;
            margin-bottom: 1.5rem;
            font-size: 1.1rem;
            line-height: 1.6;
        }

        .action-card .btn {
            padding: 0.8rem 1.5rem;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .action-card .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        }

        .action-card .btn i {
            font-size: 1.1rem;
        }

        .action-card .card-icon-bg {
            position: absolute;
            right: -20px;
            bottom: -20px;
            font-size: 8rem;
            opacity: 0.05;
            color: #2c3e50;
            transform: rotate(-15deg);
        }

        .action-card .feature-list {
            list-style: none;
            padding: 0;
            margin: 1rem 0;
        }

        .action-card .feature-list li {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
            color: #7f8c8d;
        }

        .action-card .feature-list li i {
            color: #667eea;
            font-size: 0.9rem;
        }

        .welcome-message {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .welcome-message h2 {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .welcome-message p {
            font-size: 1.1rem;
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../auth/header.php'; ?>

    <div class="container mt-4">
        <!-- Welcome Message with Logout Button -->
        <div class="welcome-message d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Welcome back, <?php echo htmlspecialchars($_SESSION['real_name']); ?>! 📚</h2>
            <form action="/_Book_Store_/logout" method="POST" class="mb-0">
                <button type="submit" 
                    class="bg-red-600 hover:bg-red-800 text-white font-bold italic text-lg px-5 py-2 rounded transition-colors duration-300">
                    Logout
                </button>
            </form>
        </div>
        <p>Your personal reading journey continues...</p>

        <div class="row mb-4">
            <!-- Clock and Date Card -->
            <div class="col-md-4 mb-4">
                <div class="card clock-card">
                    <div class="card-body text-center">
                        <div class="clock-time" id="clock">00:00:00</div>
                        <div class="clock-date" id="date">Loading...</div>
                    </div>
                </div>
            </div>

            <!-- Writer's Quote Card -->
            <div class="col-md-8 mb-4">
                <div class="card quote-card">
                    <div class="card-body">
                        <blockquote class="mb-0">
                            <p class="quote-text"><?php echo htmlspecialchars($random_quote['quote']); ?></p>
                            <footer class="blockquote-footer mt-3">
                                <cite class="quote-author"><?php echo htmlspecialchars($random_quote['author']); ?></cite>
                                <span class="quote-book"> - <?php echo htmlspecialchars($random_quote['book']); ?></span>
                            </footer>
                        </blockquote>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reading Statistics -->
        <div class="row mb-4">
            <div class="col-md-3 mb-4">
                <div class="card stat-card">
                    <div class="card-body text-center">
                        <div class="stat-icon">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="stat-value"><?php echo $stats['total_books']; ?></div>
                        <div class="stat-label">Books Read</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card stat-card">
                    <div class="card-body text-center">
                        <div class="stat-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="stat-value"><?php echo number_format($stats['average_rating'], 1); ?></div>
                        <div class="stat-label">Average Rating</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card stat-card">
                    <div class="card-body text-center">
                        <div class="stat-icon">
                            <i class="fas fa-pen-fancy"></i>
                        </div>
                        <div class="stat-value"><?php echo $stats['total_reviews']; ?></div>
                        <div class="stat-label">Reviews Written</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card stat-card">
                    <div class="card-body text-center">
                        <div class="stat-icon">
                            <i class="fas fa-bookmark"></i>
                        </div>
                        <div class="stat-value"><?php echo $counts['want_to_read']; ?></div>
                        <div class="stat-label">Want to Read</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card action-card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-search"></i>
                            Browse Books
                        </h5>
                        <p class="card-text">Explore our collection of books and discover your next favorite read.</p>
                        <ul class="feature-list">
                            <li><i class="fas fa-check-circle"></i> Search by title, author, or genre</li>
                            <li><i class="fas fa-check-circle"></i> Read book reviews and ratings</li>
                            <li><i class="fas fa-check-circle"></i> Add books to your reading list</li>
                        </ul>
                        <a href="/_Book_Store_/books" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                            Browse Books
                        </a>
                        <i class="fas fa-book-open card-icon-bg"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card action-card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-book"></i>
                            Your Library
                        </h5>
                        <p class="card-text">View and manage your personal collection of books.</p>
                        <ul class="feature-list">
                            <li><i class="fas fa-check-circle"></i> Track your reading progress</li>
                            <li><i class="fas fa-check-circle"></i> Organize books by shelves</li>
                            <li><i class="fas fa-check-circle"></i> View reading history</li>
                        </ul>
                        <a href="/_Book_Store_/library" class="btn btn-primary">
                            <i class="fas fa-book"></i>
                            View Library
                        </a>
                        <i class="fas fa-books card-icon-bg"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card action-card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-list"></i>
                            Reading List
                        </h5>
                        <p class="card-text">Keep track of books you want to read in the future.</p>
                        <ul class="feature-list">
                            <li><i class="fas fa-check-circle"></i> Save books for later</li>
                            <li><i class="fas fa-check-circle"></i> Set reading priorities</li>
                            <li><i class="fas fa-check-circle"></i> Get reading recommendations</li>
                        </ul>
                        <a href="/_Book_Store_/reading_list" class="btn btn-primary">
                            <i class="fas fa-list"></i>
                            View List
                        </a>
                        <i class="fas fa-bookmark card-icon-bg"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Update clock and date
        function updateClock() {
            const now = new Date();
            
            // Update time
            const time = now.toLocaleTimeString();
            document.getElementById('clock').textContent = time;
            
            // Update date with English format
            const options = { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric',
                locale: 'en-US'  // Force English locale
            };
            const date = now.toLocaleDateString('en-US', options);
            document.getElementById('date').textContent = date;
        }

        // Update immediately and then every second
        updateClock();
        setInterval(updateClock, 1000);

        // Add animation to cards on page load
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.dashboard-card, .stat-card, .action-card');
            cards.forEach((card, index) => {
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>

