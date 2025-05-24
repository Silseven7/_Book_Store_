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
                    <li><a class="dropdown-item" href="#" onclick="quickSave(<?php echo (int)$book['id']; ?>, 'want_to_read'); return false;">
                        <i class="fas fa-bookmark"></i> Want to Read
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="quickSave(<?php echo (int)$book['id']; ?>, 'currently_reading'); return false;">
                        <i class="fas fa-book-open"></i> Currently Reading
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="quickSave(<?php echo (int)$book['id']; ?>, 'read'); return false;">
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

<script>
function quickSave(bookId, status) {
    fetch('/_Book_Store_/update_reading_status', {
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
        showToast(data.success, data.message);
    }) 
    .catch(error => {
        console.error('Error:', error);
        showToast(false, 'Error saving book to reading list');
    });
}

function showToast(success, message) {
    const toastContainer = document.createElement('div');
    toastContainer.className = 'position-fixed bottom-0 end-0 p-3';
    toastContainer.style.zIndex = '1055';  // Bootstrap toasts use 1050+ for overlay

    toastContainer.innerHTML = `
        <div class="toast align-items-center text-bg-${success ? 'success' : 'danger'} border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;

    document.body.appendChild(toastContainer);

    // Use Bootstrap toast JS to properly initialize dismissal
    const toastEl = toastContainer.querySelector('.toast');
    const bsToast = new bootstrap.Toast(toastEl, { delay: 3000 });
    bsToast.show();

    toastEl.addEventListener('hidden.bs.toast', () => {
        toastContainer.remove();
    });
}
</script>