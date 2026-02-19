<?php
// ============================================================
// footer.php — Bottom of every dashboard page
// ============================================================
// Closes all the HTML tags that header.php opened and
// loads the shared JavaScript file.
// ============================================================
?>
        </div><!-- /.page-content -->
    </div><!-- /.main-content -->
</div><!-- /.app-wrapper -->

<!-- Delete-confirmation modal (hidden until a .delete-btn is clicked) -->
<div class="modal-overlay" id="delete-modal">
    <div class="modal-box">
        <img src="https://cdn-icons-png.flaticon.com/512/564/564619.png"
             alt="Warning" width="48" height="48"
             style="filter:invert(1);opacity:0.8;margin-bottom:12px;">
        <h3>Confirm Delete</h3>
        <p>Are you sure you want to delete this record? This action cannot be undone.</p>
        <div class="btn-group">
            <!-- Cancel just hides the modal -->
            <button class="btn btn-outline" id="cancel-delete">Cancel</button>
            <!-- Confirm submits the form that triggered the modal -->
            <button class="btn btn-danger" id="confirm-delete">Yes, Delete</button>
        </div>
    </div>
</div>

<!-- Load our shared JavaScript (sidebar toggle, modals, etc.) -->
<script src="/Apartment Management System/assets/js/main.js"></script>

</body>
</html>
