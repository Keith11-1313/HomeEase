<?php
require_once __DIR__ . '/lib/bootstrap.php';

$privacy = policy_privacy();

$page_title = 'Privacy Notice';
$page_desc  = 'Privacy notice under the Philippine Data Privacy Act of 2012 for Flynn\'s Cozy Staycation.';
$active     = 'privacy';
include __DIR__ . '/includes/public_header.php';
?>

<section class="page-head wrap">
    <p class="page-kicker"><?= icon('shield') ?> Compliance &amp; transparency</p>
    <h1>Privacy notice</h1>
    <p class="lead">In compliance with Republic Act No. 10173, also known as the Data Privacy Act of 2012 (DPA) of the Philippines, this notice explains how guest personal data and identification documents are handled.</p>
</section>

<section class="chapter">
    <div class="wrap" style="max-width:800px;">
        <article class="prose" style="display:flex;flex-direction:column;gap:1.5rem;">
            <div>
                <h2>1. What data we collect</h2>
                <p>When you request a reservation via Facebook Messenger, we ask each arriving guest to present one valid, government-issued photo ID. We collect:</p>
                <ul>
                    <li>Full name</li>
                    <li>Date of birth</li>
                    <li>Photo identification image</li>
                </ul>
            </div>

            <div>
                <h2>2. Why we collect this data</h2>
                <p><?= e($privacy['purpose']) ?> Condominium administration (Trees Residences and Milan Residenze) requires guest authorization forms and valid IDs to issue gate and lobby entry passes.</p>
            </div>

            <div>
                <h2>3. How data is stored and who has access</h2>
                <p><strong>Storage method:</strong> <?= e($privacy['storage']) ?></p>
                <p><strong>Authorized access:</strong> <?= e($privacy['access']) ?> We do not sell, rent, or transfer your personal identification to advertisers or unrelated third parties.</p>
            </div>

            <div>
                <h2>4. Data retention and deletion</h2>
                <p><strong>Retention period:</strong> <?= e($privacy['retention']) ?></p>
                <p><strong>Deletion procedure:</strong> <?= e($privacy['deletion']) ?></p>
            </div>

            <div>
                <h2>5. What this website stores</h2>
                <p><?= e($privacy['site_data']) ?></p>
                <ul>
                    <li><strong>No guest accounts:</strong> This website does not offer guest registration or member profiles.</li>
                    <li><strong>No payment details:</strong> No credit card numbers, GCash accounts, or bank details are accepted or stored on this website.</li>
                    <li><strong>No calculator logs:</strong> Quotes generated on the room calculator are calculated in real time and are not stored in any database.</li>
                    <li><strong>Cookies:</strong> This website only uses essential session cookies for property administrators logging into the content management system. No third-party tracking or advertising cookies are utilized.</li>
                </ul>
            </div>

            <div>
                <h2>6. Your rights and inquiries</h2>
                <p>Under the Philippine Data Privacy Act, you have the right to access, rectify, or request the deletion of your personal data held by us. <?= e($privacy['contact']) ?></p>
            </div>
        </article>
    </div>
</section>

<?php include __DIR__ . '/includes/public_footer.php'; ?>
