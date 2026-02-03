        </div> <!-- End main-content -->
    </div> <!-- End content -->
</div> <!-- End wrapper -->

<!-- Logout Modal -->
<div class="modal fade" id="logoutModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ready to Leave?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Select "Logout" below if you are ready to end your current session.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a class="btn btn-danger" href="logout.php">Logout</a>
            </div>
        </div>
    </div>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<!-- Bootstrap 5 Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- jsPDF & AutoTable -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.29/jspdf.plugin.autotable.min.js"></script>

<script>
    $(document).ready(function () {
        // Toggle Sidebar
        $('#sidebarCollapse').on('click', function () {
            $('#sidebar').toggleClass('active');
        });

        // Close sidebar when clicking outside on mobile (Overlay)
        $(document).on('click', '#content', function(e) {
            if ($(window).width() <= 768 && $('#sidebar').hasClass('active')) {
                // Check if the click was NOT on the sidebar toggle button
                if (!$(e.target).closest('#sidebarCollapse').length) {
                    $('#sidebar').removeClass('active');
                }
            }
        });

        // Initialize DataTables
        $('.datatable').DataTable({
            responsive: true,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search records..."
            }
        });

        // Auto-dismiss alerts after 4 seconds
        setTimeout(function() {
            $('.alert-floating').alert('close');
        }, 4000);
    });
</script>
</body>
</html>