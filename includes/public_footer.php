<?php
require_once __DIR__ . '/icons.php';

$brand     = setting('brand_name', APP_NAME);
$messenger = setting('messenger_url', 'https://m.me/');

$photoCredits = [
    [
        'title'   => 'Fairview, Novaliches, Quezon City 03',
        'creator' => 'Gdfrdymldo1999',
        'licence' => 'CC BY-SA 4.0',
        'url'     => 'https://commons.wikimedia.org/wiki/File:Fairview,_Novaliches,_Quezon_City_03.jpg',
    ],
    [
        'title'   => 'Fairview, Novaliches, Quezon City 31',
        'creator' => 'Gdfrdymldo1999',
        'licence' => 'CC BY-SA 4.0',
        'url'     => 'https://commons.wikimedia.org/wiki/File:Fairview,_Novaliches,_Quezon_City_31.jpg',
    ],
    [
        'title'   => 'Quezon City skyline at night (Quezon Avenue)',
        'creator' => 'RFNirmala',
        'licence' => 'CC0',
        'url'     => 'https://commons.wikimedia.org/wiki/File:Quezon_City_skyline_at_night_(Quezon_Avenue,_West_Avenue,_West_Triangle)_02.jpg',
    ],
    [
        'title'   => 'Quezon City skyline (Quezon Avenue) Oct2025',
        'creator' => 'RFNirmala',
        'licence' => 'CC0',
        'url'     => 'https://commons.wikimedia.org/wiki/File:Quezon_City_skyline_(Quezon_Avenue)_Oct2025.jpg',
    ],
];
?>
</main>

<footer class="site-footer">
    <div class="wrap">
        <div class="footer-close">
            <p class="footer-name"><?= logo_mark() ?><?= e($brand) ?></p>
            <div class="footer-action">
                <p>Fairview, Quezon City<br>Stay a little closer to the city.</p>
                <a class="btn btn--quiet" href="<?= e($messenger) ?>" target="_blank" rel="noopener"><?= icon('messenger') ?> Message to check availability</a>
            </div>
        </div>

        <div class="footer-grid">
            <div>
                <h3>Find us</h3>
                <p><?= icon('pin') ?> <?= e(setting('address')) ?></p>
                <p class="footer-faint"><?= e(setting('landmarks')) ?></p>
            </div>
            <div>
                <h3>Follow</h3>
                <ul class="footer-links">
                    <li><a href="https://www.instagram.com/flynnscozystaycation/" target="_blank" rel="noopener"><?= icon('instagram') ?> @flynnscozystaycation</a></li>
                    <li><a href="https://www.tiktok.com/@cozystaycation" target="_blank" rel="noopener"><?= icon('tiktok') ?> @cozystaycation</a></li>
                    <li><a href="<?= e($messenger) ?>" target="_blank" rel="noopener"><?= icon('messenger') ?> Facebook Messenger</a></li>
                </ul>
            </div>
            <div>
                <h3>Get in touch</h3>
                <ul class="footer-links">
                    <li><a href="mailto:flynnscozystay@gmail.com"><?= icon('mail') ?> flynnscozystay@gmail.com</a></li>
                    <li><span><?= icon('clock') ?> <?= e(setting('checkin_window')) ?> check-in</span></li>
                    <li><span><?= icon('key') ?> Self check-in with lockbox</span></li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <span>&copy; <?= date('Y') ?> <?= e($brand) ?></span>
            <nav aria-label="Footer">
                <a href="<?= e(url('rooms.php')) ?>">Rooms</a><span>·</span>
                <a href="<?= e(url('gallery.php')) ?>">Gallery</a><span>·</span>
                <a href="<?= e(url('faq.php')) ?>">FAQs</a>
            </nav>
            <a href="<?= e(url('admin/login.php')) ?>">Owner sign in</a>
        </div>
    </div>
</footer>

</body>
</html>
