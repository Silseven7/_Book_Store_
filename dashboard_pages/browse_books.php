<div class="card h-100">
    <div class="position-relative">
        <img src="<?php echo htmlspecialchars($book['cover_image'] ?? 'https://via.placeholder.com/300x450'); ?>" 
             class="card-img-top" 
             alt="<?php echo htmlspecialchars($book['title']); ?>"
             style="height: 300px; object-fit: cover;">
        <div class="position-absolute top-0 end-0 m-2">
            <div class="dropdown">
                <button class="btn btn-light btn-sm rounded-circle shadow-sm" 
                        type="button" 
                        data-bs-toggle="dropdown" 
                        aria-expanded="false"
                        title="Quick Save">
                    <i class="fas fa-bookmark"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="#" onclick="quickSave(<?php echo $book['id']; ?>, 'want_to_read')">
                        <i class="fas fa-bookmark"></i> Want to Read
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="quickSave(<?php echo $book['id']; ?>, 'currently_reading')">
                        <i class="fas fa-book-open"></i> Currently Reading
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="quickSave(<?php echo $book['id']; ?>, 'read')">
                        <i class="fas fa-check"></i> Read
                    </a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="card-body">
        <!-- ... existing code ... -->
    </div>
</div>

<!-- Add this before the closing </body> tag -->
<script>
function quickSave(bookId, status) {
    fetch('/_Book_Store_/api/update_reading_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            book_id: bookId,
            status: status
        })
    })
    .then(response => response.json())
    .then(data => {
        // Show success or error message
        const toast = document.createElement('div');
        toast.className = 'position-fixed bottom-0 end-0 p-3';
        toast.style.zIndex = '5';
        toast.innerHTML = `
            <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header">
                    <i class="fas ${data.success ? 'fa-check-circle text-success' : 'fa-exclamation-circle text-danger'} me-2"></i>
                    <strong class="me-auto">${data.success ? 'Success' : 'Error'}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">
                    ${data.message}
                </div>
            </div>
        `;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    })
    .catch(error => {
        console.error('Error:', error);
        // Show error message
        const toast = document.createElement('div');
        toast.className = 'position-fixed bottom-0 end-0 p-3';
        toast.style.zIndex = '5';
        toast.innerHTML = `
            <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header">
                    <i class="fas fa-exclamation-circle text-danger me-2"></i>
                    <strong class="me-auto">Error</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">
                    Error saving book to reading list
                </div>
            </div>
        `;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    });
}
</script> 