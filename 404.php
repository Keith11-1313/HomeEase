<?php
require_once __DIR__ . '/lib/bootstrap.php';
http_response_code(404);
$page_title = 'Page not found';
include __DIR__ . '/includes/public_header.php';
?>

<section class="wrap">
    <div class="empty-state">
        <span class="empty-state-label" aria-hidden="true">404</span>
        <h2>Page not found</h2>
        <p>That page doesn't exist or has moved.</p>
        <a class="btn" href="<?= e(url('/')) ?>">Go home</a>
    </div>
</section>

<?php include __DIR__ . '/includes/public_footer.php'; ?>
