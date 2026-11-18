        </div><!-- /.admin-content -->
    </div><!-- /.admin-main -->
</div><!-- /.admin-shell -->

<div id="logout-modal" hidden role="dialog" aria-modal="true" aria-labelledby="logout-modal-title" class="confirm-backdrop">
    <div class="confirm-box">
        <div class="confirm-icon confirm-icon--neutral"><?= icon('key') ?></div>
        <p id="logout-modal-title" class="confirm-msg">Sign out?</p>
        <div class="confirm-actions">
            <button type="button" id="logout-modal-cancel" class="btn btn-ghost btn-sm">Cancel</button>
            <a href="<?= e(url('admin/logout.php')) ?>" class="btn btn-danger btn-sm"><?= icon('arrow-out') ?> Sign out</a>
        </div>
    </div>
</div>

<div id="confirm-modal" hidden role="dialog" aria-modal="true" aria-labelledby="confirm-modal-msg" class="confirm-backdrop">
    <div class="confirm-box">
        <div class="confirm-icon"><?= icon('trash') ?></div>
        <p id="confirm-modal-msg" class="confirm-msg"></p>
        <div class="confirm-actions">
            <button id="confirm-modal-cancel" class="btn btn-ghost btn-sm">Cancel</button>
            <button id="confirm-modal-ok" class="btn btn-danger btn-sm">Delete</button>
        </div>
    </div>
</div>

</body>
</html>
