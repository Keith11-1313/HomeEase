<?php
require_once __DIR__ . '/lib/bootstrap.php';

$faqs = faqs_published();

$page_title = 'FAQs';
$page_desc  = 'Reservation, booking fee, security deposit, check-in, parking and pool questions answered.';
$active     = 'faqs';
include __DIR__ . '/includes/public_header.php';
?>

<section class="page-head wrap">
    <p class="page-kicker"><?= icon('check-circle') ?> Good to know</p>
    <h1>Before you book</h1>
    <p class="lead">Booking fees, check-in windows, security deposits, parking and the pool — answered the way we answer them in chat.</p>
</section>

<?php if ($faqs): ?>
    <section class="chapter faq-page-list">
        <div class="wrap faq-layout">
            <h2 class="reveal">Questions, in full.</h2>

            <div class="faq-list reveal" style="--d:80ms">
                <?php foreach ($faqs as $faq): ?>
                    <details class="faq">
                        <summary><?= e($faq['question']) ?></summary>
                        <p><?= nl2br(e($faq['answer'])) ?></p>
                    </details>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php else: ?>
    <div class="wrap">
        <div class="empty-state">
            <span class="empty-state-label" aria-hidden="true">—</span>
            <h2>No FAQs yet</h2>
            <p>Questions and answers are being added. Send yours on Messenger.</p>
            <a class="btn" href="<?= e(setting('messenger_url', 'https://m.me/')) ?>" target="_blank" rel="noopener"><?= icon('messenger') ?> Ask directly</a>
        </div>
    </div>
<?php endif; ?>

<section class="chapter chapter--ink cta faq-cta" data-field="dark">
    <div class="wrap">
        <h2 class="reveal">Still deciding?</h2>
        <div class="cta-foot reveal" style="--d:80ms">
            <p>Send the date and the room you like. We will confirm availability and the exact total.</p>
            <a class="btn btn--accent" href="<?= e(setting('messenger_url', 'https://m.me/')) ?>" target="_blank" rel="noopener"><?= icon('messenger') ?> Message us</a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/public_footer.php'; ?>
