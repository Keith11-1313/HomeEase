<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/bootstrap.php';
require_once __DIR__ . '/../lib/auth.php';
require_admin();

if (is_post()) {
    csrf_verify();
    $action = post('action');
    $id     = (int)post('id');

    if ($action === 'add') {
        $guestName  = post('guest_name');
        $body       = post('body');
        $rating     = max(1, min(5, (int)post('rating', '5')));
        $reviewDate = post('review_date') ?: null;
        $source     = post('source');
        $published  = isset($_POST['is_published']);
        $sortOrder  = (int)post('sort_order', '0');

        if ($guestName === '' || $body === '') {
            flash('error', 'Guest name and review body are required.');
        } else {
            try {
                $stmt = db()->prepare(
                    'INSERT INTO reviews (guest_name, review_date, rating, body, source, is_published, sort_order)
                     VALUES (:guest_name, :review_date, :rating, :body, :source, :published, :sort)
                     RETURNING id'
                );
                $stmt->execute([
                    'guest_name'  => $guestName,
                    'review_date' => $reviewDate,
                    'rating'      => $rating,
                    'body'        => $body,
                    'source'      => $source !== '' ? $source : null,
                    'published'   => $published,
                    'sort'        => $sortOrder,
                ]);
                audit('create', 'reviews', (string)$stmt->fetchColumn(), null, ['guest_name' => $guestName]);
                flash('success', 'Review added.');
            } catch (Throwable $e) {
                flash('error', 'Could not save review: ' . $e->getMessage());
            }
        }
    } elseif ($action === 'delete' && $id > 0) {
        db()->prepare('DELETE FROM reviews WHERE id = :id')->execute(['id' => $id]);
        audit('delete', 'reviews', (string)$id);
        flash('success', 'Review deleted.');
    } elseif ($action === 'toggle' && $id > 0) {
        db()->prepare('UPDATE reviews SET is_published = NOT is_published WHERE id = :id')->execute(['id' => $id]);
        audit('update', 'reviews', (string)$id, null, ['is_published' => 'toggled']);
        flash('success', 'Visibility updated.');
    }

    redirect('admin/reviews.php');
}

$reviews = reviews_all();

$page_title = 'Reviews';
$active     = 'reviews.php';
include __DIR__ . '/../includes/admin_header.php';
?>

<div class="card">
    <div class="card-head">
        <div>
            <h2><?= icon('users') ?> Guest reviews</h2>
            <p class="card-sub">Published reviews appear in the testimonials strip on the homepage. Unpublished reviews are saved but not shown to guests.</p>
        </div>
    </div>

    <?php if ($reviews): ?>
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>Guest</th>
                        <th>Rating</th>
                        <th>Review</th>
                        <th>Date</th>
                        <th>Source</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reviews as $rev): ?>
                        <tr>
                            <td><strong><?= e($rev['guest_name']) ?></strong></td>
                            <td><?= str_repeat('★', (int)$rev['rating']) ?> (<?= (int)$rev['rating'] ?>/5)</td>
                            <td><p style="margin:0;max-width:350px;white-space:pre-wrap;"><?= e($rev['body']) ?></p></td>
                            <td class="nowrap"><?= e($rev['review_date'] ?? '—') ?></td>
                            <td><?= e($rev['source'] ?: 'Direct') ?></td>
                            <td>
                                <span class="pill <?= $rev['is_published'] ? '' : 'pill-off' ?>">
                                    <?= $rev['is_published'] ? 'Published' : 'Hidden' ?>
                                </span>
                            </td>
                            <td>
                                <div class="row-actions">
                                    <form class="inline" method="post" action="<?= e(url('admin/reviews.php')) ?>">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= (int)$rev['id'] ?>">
                                        <input type="hidden" name="action" value="toggle">
                                        <button class="btn btn-ghost btn-sm" type="submit">
                                            <?= $rev['is_published'] ? icon('close') . ' Hide' : icon('check') . ' Show' ?>
                                        </button>
                                    </form>
                                    <form class="inline" method="post" action="<?= e(url('admin/reviews.php')) ?>"
                                          data-confirm="Delete this review?">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= (int)$rev['id'] ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button class="btn btn-danger btn-sm" type="submit"><?= icon('trash') ?> Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="admin-empty">
            <?= icon('users') ?>
            <p>No reviews yet.</p>
            <p class="admin-empty-sub">Once guests leave feedback, add it below. Published reviews show on the homepage automatically.</p>
        </div>
    <?php endif; ?>
</div>

<div class="card" style="margin-top:2rem;">
    <div class="card-head">
        <div><h2><?= icon('plus') ?> Add a guest review</h2></div>
    </div>

    <form method="post" action="<?= e(url('admin/reviews.php')) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="add">

        <div style="display:grid;gap:1rem;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));">
            <label class="field">
                <span>Guest name or initials</span>
                <input type="text" name="guest_name" required placeholder="e.g. Maria C.">
            </label>

            <label class="field">
                <span>Rating</span>
                <select name="rating">
                    <option value="5" selected>5 Stars (★★★★★)</option>
                    <option value="4">4 Stars (★★★★☆)</option>
                    <option value="3">3 Stars (★★★☆☆)</option>
                    <option value="2">2 Stars (★★☆☆☆)</option>
                    <option value="1">1 Star (★☆☆☆☆)</option>
                </select>
            </label>

            <label class="field">
                <span>Review date (optional)</span>
                <input type="date" name="review_date" value="<?= date('Y-m-d') ?>">
            </label>

            <label class="field">
                <span>Source (optional)</span>
                <input type="text" name="source" placeholder="e.g. Google, Airbnb, Facebook">
            </label>
        </div>

        <label class="field" style="margin-top:1rem;">
            <span>Review text</span>
            <textarea name="body" rows="3" required placeholder="Paste guest feedback here..."></textarea>
        </label>

        <div style="margin-top:1rem;display:flex;align-items:center;gap:1.5rem;">
            <label class="check">
                <input type="checkbox" name="is_published" value="1" checked>
                <span>Publish immediately</span>
            </label>
            <button class="btn" type="submit"><?= icon('plus') ?> Add review</button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
