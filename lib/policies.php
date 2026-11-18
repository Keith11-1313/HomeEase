<?php
declare(strict_types=1);

// ============================================================
// policies.php — single source of truth for all policy wording
// ============================================================

// --- booking fee & security deposit -------------------------
define('BOOKING_FEE_AMOUNT',      '₱500');
define('SECURITY_DEPOSIT_AMOUNT', '₱500');
define('TOTAL_ADVANCE_AMOUNT',    '₱500');

function policy_booking_fee(): string
{
    return 'A ' . BOOKING_FEE_AMOUNT . ' booking fee secures your slot and is paid online after confirming through Messenger.';
}

function policy_security_deposit(): string
{
    return 'A refundable security deposit of ' . SECURITY_DEPOSIT_AMOUNT
        . ' is collected separately and returned after check-out, provided there are no damages, missing items, or rule violations.';
}

function policy_deposit_short(): string
{
    return 'The security deposit is refundable after check-out if there are no damages, missing items, or violations.';
}

// --- cancellation & reschedule ------------------------------

function policy_cancellation(): array
{
    return [
        'more_than_7' => 'Full refund of the booking fee.',
        'within_7'    => 'The booking fee is forfeited. No additional charges.',
        'no_show'     => 'The booking fee is forfeited and the full stay rate may be charged.',
        'reschedule'  => 'One free reschedule if requested more than 48 hours before check-in. The booking fee is not transferable to another guest.',
    ];
}

function policy_cancellation_summary(): string
{
    $terms = policy_cancellation();
    return 'More than 7 days before check-in: ' . $terms['more_than_7']
        . ' Within 7 days: ' . $terms['within_7']
        . ' No-show: ' . $terms['no_show']
        . ' Rescheduling: ' . $terms['reschedule'];
}

// --- check-in windows per slot ------------------------------

function policy_checkin_overnight(): string
{
    return setting('checkin_overnight', '6:00 PM – 9:00 PM');
}

function policy_checkin_daycation(): string
{
    return setting('checkin_daycation', '7:00 AM – 9:00 AM');
}

function policy_checkout_overnight(): string
{
    return setting('checkout_overnight', '7:00 AM');
}

function policy_checkout_daycation(): string
{
    return setting('checkout_daycation', '7:00 PM');
}

function policy_checkin_display(): string
{
    return 'Overnight: ' . policy_checkin_overnight() . ' · Daycation: ' . policy_checkin_daycation();
}

// --- house rules --------------------------------------------

function policy_house_rules(): array
{
    return [
        'quiet_hours'    => '8:00 PM – 9:00 PM onwards (keep videoke and general noise low as courtesy to neighbors)',
        'smoking'        => 'Strictly no smoking or vaping inside the unit (tobacco, cannabis, vape). Penalty: ₱3,500.',
        'pets'           => 'No pets allowed.',
        'visitors'       => 'Only pre-registered guests permitted; no unregistered visitors. Penalty: ₱1,000 per unregistered head.',
        'max_noise'      => 'No parties or loud gatherings. Videoke and loud noise must stop by 8:00 PM – 9:00 PM.',
        'cleanliness'    => 'Maintain cleanliness at all times (CLAYGO). Do not leave unwashed oily dishes in the sink. Penalty for untidy unit: ₱500.',
        'trash'          => 'Dispose of trash properly in the garbage room. Penalty: ₱250.',
        'cooking'        => 'Light cooking only. Avoid strong-smelling foods (shrimp, tuyo, seafood, curry) and liniment oils. Penalty: ₱500.',
        'massage'        => 'In-room massage sessions are not allowed to protect linens and mattresses from oil stains and strong odor. Penalty: ₱1,500.',
        'checkout'       => 'Check out on time. A ₱500 fee applies for check-out delays over 3 minutes.',
        'trees_rules'    => 'Follow all Trees Residences building rules. Present a valid ID at the security desk upon entry.',
        'milan_rules'    => 'Follow all Milan Residenze building rules. Present a valid ID at the security desk upon entry.',
        'parking'        => 'Building parking is limited. Reserved parking near the building must be requested in advance.',
        'damages'        => 'Guests are accountable for any dirt, damage, stains, or losses during the stay. Costs will be deducted from the security deposit or charged separately.',
        'checkout_clean' => 'Turn off aircon and appliances. Ensure water is not running, dishes are washed, trash is disposed in the garbage room, and doors are securely locked.',
    ];
}

// --- privacy (Philippine Data Privacy Act) ------------------

function policy_privacy(): array
{
    return [
        'data_collected'   => 'Full name, government-issued ID photo, and date of birth of each guest.',
        'purpose'          => 'Guest identity verification and compliance with condominium building security requirements.',
        'storage'          => 'Stored in a private, password-protected folder accessible only to the owner.',
        'access'           => 'Only the property owner and authorised building security personnel.',
        'retention'        => 'ID images are deleted within 30 days after check-out.',
        'deletion'         => 'Guests may request early deletion by messaging us on Facebook Messenger. We will confirm deletion within 7 business days.',
        'contact'          => 'For data privacy requests, message us on Facebook Messenger or email flynnscozystay@gmail.com.',
        'site_data'        => 'This website does not create guest accounts, store personal data, or use tracking cookies. The quote calculator does not save submissions.',
    ];
}
