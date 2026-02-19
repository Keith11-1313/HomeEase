// ============================================================
// main.js — HomeEase Shared JavaScript
// ============================================================
// Handles the sidebar toggle, notification dropdown, user
// dropdown, confirm-delete modals, and auto-dismiss flash
// messages.  Uses only Vanilla JS — no frameworks.
// ============================================================


// --------------------------------------------------------
// Wait until the entire HTML page has finished loading
// before we try to find elements on the page.
// --------------------------------------------------------
document.addEventListener('DOMContentLoaded', function () {

    // ======================================================
    // 1.  SIDEBAR TOGGLE  (mobile)
    // ======================================================
    // On small screens the sidebar is hidden off-screen.
    // Tapping the hamburger button slides it in.
    // ======================================================

    // Find the hamburger button and sidebar elements.
    var hamburger = document.getElementById('hamburger-btn');
    var sidebar = document.getElementById('sidebar');
    var sidebarClose = document.getElementById('sidebar-overlay');

    // Only set up the listener if the button exists (it won't
    // exist on the login page).
    if (hamburger && sidebar) {
        hamburger.addEventListener('click', function () {
            // "toggle" adds the class if it is missing, or removes
            // it if it is already there.
            sidebar.classList.toggle('open');
            if (sidebarClose) sidebarClose.classList.toggle('show');
        });
    }

    // Close sidebar when the dark overlay is clicked.
    if (sidebarClose) {
        sidebarClose.addEventListener('click', function () {
            sidebar.classList.remove('open');
            sidebarClose.classList.remove('show');
        });
    }


    // ======================================================
    // 2.  USER DROPDOWN
    // ======================================================
    // Clicking the user avatar area in the topbar opens a
    // small dropdown menu with "Profile" and "Logout".
    // ======================================================
    var userMenu = document.getElementById('user-menu');
    var userDropdown = document.getElementById('user-dropdown');

    if (userMenu && userDropdown) {
        userMenu.addEventListener('click', function (e) {
            // Prevent the click from immediately closing the menu
            // (because of the document-level listener below).
            e.stopPropagation();
            userDropdown.classList.toggle('show');
        });

        // Close the dropdown when clicking anywhere else.
        document.addEventListener('click', function () {
            userDropdown.classList.remove('show');
        });
    }


    // ======================================================
    // 3.  CONFIRM-DELETE MODALS
    // ======================================================
    // Any button with the class "delete-btn" will show a
    // confirmation modal before actually deleting.
    // ======================================================
    var deleteBtns = document.querySelectorAll('.delete-btn');
    var modalOverlay = document.getElementById('delete-modal');
    var confirmBtn = document.getElementById('confirm-delete');
    var cancelBtn = document.getElementById('cancel-delete');

    // We store which form should be submitted when the user
    // clicks "Yes, Delete" in the modal.
    var formToSubmit = null;

    deleteBtns.forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            // Stop the form from submitting right away.
            e.preventDefault();

            // The button sits inside a <form>, which is what we
            // actually want to submit after confirmation.
            formToSubmit = btn.closest('form');

            // Show the modal.
            if (modalOverlay) modalOverlay.classList.add('show');
        });
    });

    // "Yes, Delete" — submit the stored form.
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function () {
            if (formToSubmit) formToSubmit.submit();
        });
    }

    // "Cancel" — just hide the modal.
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function () {
            if (modalOverlay) modalOverlay.classList.remove('show');
            formToSubmit = null;
        });
    }


    // ======================================================
    // 4.  AUTO-DISMISS FLASH MESSAGES
    // ======================================================
    // Flash messages (success, error, etc.) will fade out and
    // remove themselves after 4 seconds so the user doesn't
    // have to manually close them.
    // ======================================================
    var flashMessages = document.querySelectorAll('.flash-message');

    flashMessages.forEach(function (msg) {
        setTimeout(function () {
            // Start fading out.
            msg.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
            msg.style.opacity = '0';
            msg.style.transform = 'translateY(-10px)';

            // After the fade animation finishes, remove the
            // element from the page entirely.
            setTimeout(function () {
                msg.remove();
            }, 400);
        }, 4000);  // 4 000 milliseconds = 4 seconds
    });


    // ======================================================
    // 5.  FAQ ACCORDION
    // ======================================================
    // Each FAQ question toggles its answer panel open/closed.
    // ======================================================
    var faqQuestions = document.querySelectorAll('.faq-question');

    faqQuestions.forEach(function (btn) {
        btn.addEventListener('click', function () {
            // "nextElementSibling" is the .faq-answer div right
            // after the button.
            var answer = btn.nextElementSibling;

            // Toggle the "open" class on the button (rotates the
            // arrow icon).
            btn.classList.toggle('open');

            // If the answer is currently showing, close it.
            if (answer.style.maxHeight) {
                answer.style.maxHeight = null;
            } else {
                // scrollHeight is the full height of the content
                // even if it is hidden.  Setting maxHeight to this
                // value makes the CSS transition smoothly slide it
                // open.
                answer.style.maxHeight = answer.scrollHeight + 'px';
            }
        });
    });

});
