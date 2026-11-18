<?php
declare(strict_types=1);

require_once __DIR__ . '/repo.php';

// ============================================================
// calc.php — quote calculator
// ============================================================
// Produces a line-item estimate only. Nothing is stored and no
// transaction happens here; the CTA hands off to Messenger.
// ============================================================

function slot_label(string $slot): string
{
    return $slot === 'daycation' ? 'Daycation' : 'Overnight';
}

function day_type_label(string $dayType): string
{
    return $dayType === 'weekend_holiday' ? 'weekend / holiday' : 'weekday';
}

/**
 * @throws RuntimeException when the room has no usable rate.
 */
function calculate_quote(array $input): array
{
    $unitId = (int)($input['unit_id'] ?? 0);
    $unit   = unit_by_id($unitId);

    if (!$unit || !$unit['is_published']) {
        throw new RuntimeException('That room is not available.');
    }

    // --- sanitise inputs ------------------------------------
    $date  = (string)($input['date'] ?? date('Y-m-d'));
    $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
    if (!$parsed || $parsed->format('Y-m-d') !== $date) {
        $date = date('Y-m-d');
    }

    $slot = (string)($input['slot'] ?? 'overnight');
    if (!in_array($slot, ['overnight', 'daycation', 'flexible'], true)) {
        $slot = 'overnight';
    }

    $hours = max(1, min(72, (int)($input['hours'] ?? 12)));
    $pax   = max(1, min(10, (int)($input['pax'] ?? 2)));
    $pool  = !empty($input['pool']);

    $parking = (string)($input['parking'] ?? 'none');
    if (!in_array($parking, ['none', 'parking_reserved_motor', 'parking_reserved_car', 'parking_pay_motor', 'parking_pay_car'], true)) {
        $parking = 'none';
    }

    $dayType = day_type_for($date);

    // --- pick the base rate ---------------------------------
    $base = null;
    $fallback = null;

    foreach (['slot' => $slot, 'flexible' => 'flexible'] as $candidateSlot) {
        foreach (unit_rates($unitId) as $rate) {
            if ($rate['day_type'] !== $dayType || $rate['slot_type'] !== $candidateSlot) {
                continue;
            }
            $rateHours = (int)$rate['hours'];
            if ($rateHours === $hours) {
                $base = $rate;
                break 2;
            }
            if ($rateHours <= $hours && ($fallback === null || $rateHours > (int)$fallback['hours'])) {
                $fallback = $rate;
            }
        }
        if ($fallback !== null) {
            break;
        }
    }

    if ($base === null) {
        $base = $fallback;
    }
    if ($base === null) {
        throw new RuntimeException('No rate is configured for this room yet. Please message us.');
    }

    $baseHours  = (int)$base['hours'];
    $extraHours = max(0, $hours - $baseHours);

    // --- add-on prices --------------------------------------
    $addons = [];
    foreach (addons_all(true) as $addon) {
        $addons[$addon['code']] = $addon;
    }

    $extraHourRate  = (float)($addons['extra_hour']['amount'] ?? 0);
    $extraPaxRate   = (float)($addons['extra_pax']['amount'] ?? 0);
    $includedPax    = max(1, (int)setting('included_pax', '2'));
    $extraPax       = max(0, $pax - $includedPax);

    // --- build the line items -------------------------------
    $items = [];

    $items[] = [
        'label'  => sprintf('%d-hour %s', $baseHours, strtolower(slot_label($slot))),
        'note'   => day_type_label($dayType),
        'amount' => (float)$base['amount'],
    ];

    if ($extraHours > 0) {
        $items[] = [
            'label'  => sprintf('Additional %d hour%s', $extraHours, $extraHours === 1 ? '' : 's'),
            'note'   => money($extraHourRate, false) . ' each',
            'amount' => $extraHours * $extraHourRate,
        ];
    }

    if ($extraPax > 0) {
        $items[] = [
            'label'  => sprintf('Additional %d pax', $extraPax),
            'note'   => money($extraPaxRate, false) . ' each · 2 included',
            'amount' => $extraPax * $extraPaxRate,
        ];
    }

    if ($pool) {
        $poolMap = pool_rates($unitId);
        if (isset($poolMap[$dayType])) {
            $items[] = [
                'label'  => 'Pool access',
                'note'   => $unit['name'] . ' rate',
                'amount' => (float)$poolMap[$dayType],
            ];
        }
    }

    if ($parking !== 'none' && isset($addons[$parking])) {
        $items[] = [
            'label'  => $addons[$parking]['label'],
            'note'   => $addons[$parking]['unit_label'] ?? '',
            'amount' => (float)$addons[$parking]['amount'],
        ];
    }

    $total = array_sum(array_column($items, 'amount'));

    // --- refundable deposit (shown separately) --------------
    $deposit = deposit_for($hours);

    return [
        'unit'        => [
            'id'     => (int)$unit['id'],
            'name'   => $unit['name'],
            'slug'   => $unit['slug'],
            'tagline'=> $unit['tagline'],
        ],
        'date'        => $date,
        'day_type'    => $dayType,
        'day_label'   => day_type_label($dayType),
        'slot'        => $slot,
        'slot_label'  => slot_label($slot),
        'hours'       => $hours,
        'pax'         => $pax,
        'items'       => $items,
        'total'       => $total,
        'deposit'     => $deposit ? (float)$deposit['amount'] : null,
        'deposit_label' => $deposit['label'] ?? null,
        'total_with_deposit' => $total + ($deposit ? (float)$deposit['amount'] : 0),
    ];
}

/** Human-readable quote used to prefill Facebook Messenger. */
function messenger_message(array $quote): string
{
    $lines = [
        sprintf(
            'Hi! I would like to ask about %s (%s).',
            $quote['unit']['name'],
            $quote['unit']['tagline'] ?: 'studio stay'
        ),
        sprintf(
            '%s · %s · %dh · %d pax · %s',
            format_date($quote['date']),
            $quote['slot_label'],
            $quote['hours'],
            $quote['pax'],
            $quote['day_label']
        ),
        'Estimate: ' . money($quote['total']),
    ];

    if ($quote['deposit'] !== null) {
        $lines[] = 'Security deposit (refundable): ' . money($quote['deposit']);
    }

    $lines[] = 'Is this slot available?';

    return implode("\n", $lines);
}

function messenger_link(array $quote): string
{
    $base = setting('messenger_url', 'https://m.me/');
    $sep  = str_contains($base, '?') ? '&' : '?';
    return $base . $sep . 'text=' . rawurlencode(messenger_message($quote));
}
