<!-- Show Image Modal   -->
<div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="image-title"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="previewImage" src="" class="img-fluid rounded" alt="Preview">
            </div>
        </div>
    </div>
</div>


<!-- Show the Content -->
<div class="modal fade" id="showContentModal" tabindex="-1" aria-labelledby="showContentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="showContentModalLabel">Title</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                <!-- content will appear here -->
            </div>
        </div>
    </div>
</div>


<script>
    // Click handler for any element with class 'viewModalBtn'
    $(document).on('click', '.viewModalBtn', function() {
        const title = $(this).data('title'); // e.g., "Remark" or "Description"
        const content = $(this).data('content'); // raw text

        showPlainTextModal(title, content);
    });

    function showPlainTextModal(title, text) {
        const $modal = $('#showContentModal');
        if (!$modal.length) return;


        $('#showContentModalLabel').text(title || '');

        $modal.find('.modal-body')
            .text(text || '')
            .css({
                'white-space': 'pre-wrap', // preserves newlines
                'word-wrap': 'break-word', // wrap long words
                'font-family': 'inherit' // optional: inherit font
            });

        // Show the modal
        $modal.modal('show');
    }
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('sidebarToggle');
        const closeBtn = document.getElementById('sidebarClose');

        if (!sidebar || !toggleBtn) return;

        function updateSidebarState() {
            if (window.innerWidth < 768) {
                sidebar.classList.add('collapsed');
            } else {
                sidebar.classList.remove('collapsed');
            }
        }

        // Initial state
        updateSidebarState();

        // Toggle sidebar
        toggleBtn.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
        });

        // Close sidebar
        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                sidebar.classList.add('collapsed');
            });
        }

        // On window resize
        window.addEventListener('resize', updateSidebarState);
    });

    function logoutConfirmation() {
        Swal.fire({
            title: 'Are you sure?',
            text: "You will be logged out from your account!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Logout',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                //  window.location.href = '/logout'; 
            }
        });
    }

    function showImage(src, title = "Image Preview") {
        $('#previewImage').attr('src', src);
        $('#image-title').html(title);
        $('#imagePreviewModal').modal('show');
    }
</script>


@if(session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Success',
        text: "{{ session('success') }}",
        timer: 3000,
        showConfirmButton: false
    });
</script>
@endif

@if(session('error'))
<script>
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: "{{ session('error') }}",
    });
</script>
@endif

@if(session('info'))
<script>
    Swal.fire({
        icon: 'info',
        title: 'Info',
        text: "{{ session('info') }}",
    });
</script>
@endif


<script>
    document.querySelectorAll('.raise-request-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            Swal.fire({
                title: 'Are you sure?',
                text: 'Do you want to raise this service request?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, Send',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData(form);
                    fetch(form.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire('Success', 'Service request raised successfully!',
                                    'success');
                                const button = form.querySelector('button');
                                button.textContent = 'Requested';
                                button.className = 'btn btn-secondary btn-sm w-100';
                                button.disabled = true;
                                form.removeEventListener('submit', arguments.callee);
                            } else {
                                Swal.fire('Error', data.message ||
                                    'Failed to raise request', 'error');
                            }
                        })
                        .catch(error => {
                            Swal.fire('Error', 'Network error occurred', 'error');
                        });
                }
            });
        });
    });
    document.querySelectorAll('.approve-request-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            Swal.fire({
                title: 'Confirm Activation',
                text: 'Do you want to activate this service?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Activate',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });

    // Format status like pending => Pending
    function formatStatus(status) {
        if (!status) return '';

        return status
            .toLowerCase()
            .replace(/^\w/, c => c.toUpperCase());
    }

    // Format daeTime like  formatDateTime(dateValue) => Jan-27 2026 03:14 pm
    function formatDateTime(dateValue) {
        if (!dateValue) return '';

        const date = new Date(dateValue);

        if (isNaN(date)) return '';

        const month = date.toLocaleString('en-US', {
            month: 'short'
        });

        const day = String(date.getDate()).padStart(2, '0');

        const year = date.getFullYear();

        let hours = date.getHours();
        const minutes = String(date.getMinutes()).padStart(2, '0');
        const ampm = hours >= 12 ? 'pm' : 'am';
        hours = hours % 12;
        hours = hours ? hours : 12; // convert 0 => 12

        return `${month}-${day}-${year} ${hours}:${minutes} ${ampm}`;
    }
</script>