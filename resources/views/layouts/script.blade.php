<!-- Show Image Modal   -->
<div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="image-title"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="previewImage" src="" class="img-fluid rounded" alt="Preview">
            </div>
        </div>
    </div>
</div>


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


    function changeStatus(route, text = 'This Record') {
        Swal.fire({
            title: 'Are you sure to change status of ' + text + '?',
            // text: "You will be logged out from your account!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes',
            cancelButtonText: 'No'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = route;
            }
        });
    }

    function showImage(src, title = "Image Preview") {
        $('#previewImage').attr('src', src);
        $('#image-title').html(title);
        $('#imagePreviewModal').modal('show');
    }
</script>