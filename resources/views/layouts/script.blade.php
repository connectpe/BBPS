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
 </script>

