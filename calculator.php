<?php
declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';
require_once __DIR__ . '/lib/calc.php';

header('Content-Type: application/json; charset=utf-8');

if (!is_post()) {
    http_response_code(405);
    echo json_encode(['error' => 'Use POST.']);
    exit;
}

try {
    $quote = calculate_quote($_POST);
    $quote['currency']     = CURRENCY;
    $quote['date_label']   = format_date($quote['date']);
    $quote['messenger_url'] = messenger_link($quote);

    echo json_encode($quote, JSON_UNESCAPED_UNICODE);
} catch (Throwable $error) {
    http_response_code(422);
    echo json_encode([
        'error' => APP_DEBUG ? $error->getMessage() : 'Could not calculate that quote right now.',
    ], JSON_UNESCAPED_UNICODE);
}
