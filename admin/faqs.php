<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/bootstrap.php';
require_once __DIR__ . '/../lib/auth.php';
require_admin();

if (is_post()) {
    csrf_verify();
    $action = post('action');
    $id     = (int)post('id');

    if ($action === 'save' && $id > 0) {
        $question = post('question');
        $answer   = post('answer');

        if ($question === '' || $answer === '') {
            flash('error', 'A question and an answer are both required.');
        } else {
            try {
                db()->prepare(
                    'UPDATE faqs
                        SET question = :question, answer = :answer,
                            sort_order = :sort, is_published = :published
                      WHERE id = :id'
                )->execute([
                    'question'  => $question,
                    'answer'    => $answer,
                    'sort'      => (int)post('sort_order'),
                    'published' => isset($_POST['is_published']),
                    'id'        => $id,
                ]);
                audit('update', 'faqs', (string)$id, null, ['question' => $question]);
                flash('success', 'FAQ updated.');
            } catch (PDOException $e) {
                flash('error', 'That question already exists.');
            }
        }
    }

    if ($action === 'add') {
        $question = post('question');
        $answer   = post('answer');

        if ($question === '' || $answer === '') {
            flash('error', 'A question and an answer are both required.');
        } else {
            try {
                $stmt = db()->prepare(
                    'INSERT INTO faqs (question, answer, sort_order, is_published)
                     VALUES (:question, :answer, :sort, :published)
                     RETURNING id'
                );
                $stmt->execute([
                    'question'  => $question,
                    'answer'    => $answer,
                    'sort'      => (int)post('sort_order'),
                    'published' => isset($_POST['is_published']),
                ]);
                audit('create', 'faqs', (string)$stmt->fetchColumn(), null, ['question' => $question]);
                flash('success', 'FAQ added.');
            } catch (PDOException $e) {
                flash('error', 'That question already exists.');
            }
        }
    }

    if ($action === 'toggle' && $id > 0) {
        db()->prepare('UPDATE faqs SET is_published = NOT is_published WHERE id = :id')
            ->execute(['id' => $id]);
        audit('update', 'faqs', (string)$id, null, ['is_published' => 'toggled']);
        flash('success', 'Visibility updated.');
    }

    if ($action === 'delete' && $id > 0) {
        db()->prepare('DELETE FROM faqs WHERE id = :id')->execute(['id' => $id]);
        audit('delete', 'faqs', (string)$id);
        flash('success', 'FAQ deleted.');
    }

    redirect('admin/faqs.php');
}

$faqs = faqs_all();

$page_title = 'FAQs';
$active     = 'faqs.php';
include __DIR__ . '/../includes/admin_header.php';
?>

<div class="card">
    <div class="card-head">
        <div>
            <h2><?= icon('mail') ?> Public questions</h2>
        </div>
    </div>

    <?php if ($faqs): ?>
        <?php foreach ($faqs as $faq): ?>
            <form method="post" action="<?= e(url('admin/faqs.php')) ?>" class="faq-edit">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int)$faq['id'] ?>">

                <label class="field">
                    <span>Question</span>
                    <input type="text" name="question" value="<?= e($faq['question']) ?>" required>
                </label>

                <label class="field">
                    <span>Answer</span>
                    <textarea name="answer" required><?= e($faq['answer']) ?></textarea>
                </label>

                <div class="grid-3">
                    <label class="field">
                        <span>Sort order</span>
                        <input type="number" name="sort_order" value="<?= (int)$faq['sort_order'] ?>">
                    </label>

                    <label class="field" style="display:flex;align-items:center;gap:.5rem;padding-top:1.2rem">
                        <input type="checkbox" name="is_published" value="1" <?= checked((bool)$faq['is_published']) ?>>
                        Published
                    </label>

                    <div class="row-actions" style="align-self:end;padding-bottom:.9rem">
                        <button class="btn btn-sm" type="submit" name="action" value="save"><?= icon('check') ?> Save</button>
                        <button class="btn btn-danger btn-sm" type="submit" name="action" value="delete" formnovalidate><?= icon('close') ?> Delete</button>
                    </div>
                </div>
            </form>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="empty">No FAQs yet.</p>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-head">
        <div><h2><?= icon('plus') ?> Add a question</h2></div>
    </div>

    <form method="post" action="<?= e(url('admin/faqs.php')) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="add">

        <label class="field">
            <span>Question</span>
            <input type="text" name="question" required>
        </label>

        <label class="field">
            <span>Answer</span>
            <textarea name="answer" required></textarea>
        </label>

        <div class="grid-3">
            <label class="field">
                <span>Sort order</span>
                <input type="number" name="sort_order" value="0">
            </label>

            <label class="field" style="display:flex;align-items:center;gap:.5rem;padding-top:1.2rem">
                <input type="checkbox" name="is_published" value="1" checked>
                Publish immediately
            </label>
        </div>

        <button class="btn" type="submit"><?= icon('plus') ?> Add FAQ</button>
    </form>
</div>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
