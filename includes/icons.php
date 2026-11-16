<?php
declare(strict_types=1);

// ============================================================
// icons.php â€” external Tabler SVG assets + brand lockups
// ============================================================

/** Render a vendored Tabler icon as an external SVG image. */
function icon(string $name, string $class = '', int $size = 20): string
{
    $classes = trim('icon ' . $class);
    $name = preg_replace('/[^a-z0-9-]/', '', $name);
    $path = __DIR__ . '/../assets/icons/' . $name . '.svg';
    if (!is_file($path)) {
        $name = 'check';
    }
    return '<img class="' . e($classes) . '" src="' . e(asset('icons/' . $name . '.svg')) . '" width="' . $size . '" height="' . $size . '" alt="" aria-hidden="true">';
}

/** Pick a fitting icon from an amenity's name. */
function amenity_icon(string $name): string
{
    $name = strtolower($name);

    $groups = [
        'snow'         => ['aircon', 'air con', 'condition', 'cooling'],
        'wifi'         => ['wifi', 'internet', 'fiber'],
        'tv'           => ['tv', 'netflix', 'screen', 'smart'],
        'kitchen'      => ['kitchen', 'ref', 'fridge', 'microwave', 'stove', 'cook'],
        'shower'       => ['shower', 'bath', 'heater', 'towels'],
        'bed'          => ['bed', 'linen', 'mattress', 'pillow'],
        'car'          => ['parking', 'car', 'garage'],
        'waves'        => ['pool', 'swim'],
        'coffee'       => ['coffee', 'kettle', 'drink'],
        'shield'       => ['security', 'cctv', 'guard', 'safe'],
        'key'          => ['key', 'lock', 'door', 'check-in', 'check in'],
        'sparkle'      => ['clean', 'housekeep', 'towel'],
        'lamp'         => ['desk', 'work', 'lamp', 'light'],
        'building'     => ['building', 'floor', 'lift', 'elevator'],
        'users'        => ['guest', 'pax', 'person'],
    ];

    foreach ($groups as $icon => $needles) {
        foreach ($needles as $needle) {
            if (str_contains($name, $needle)) {
                return $icon;
            }
        }
    }
    return 'check';
}

/** Fact chips shared by the home list, room list and room page. */
function unit_fact_items(array $unit): array
{
    $size = $unit['size_sqm'] ? rtrim(rtrim((string)$unit['size_sqm'], '0'), '.') : null;

    $facts = [];
    if (!empty($unit['building_name'])) {
        $facts[] = ['icon' => 'building', 'text' => (string)$unit['building_name']];
    }
    if ($size !== null && $size !== '') {
        $facts[] = ['icon' => 'ruler', 'text' => $size . ' sqm'];
    }
    if (!empty($unit['bed_type'])) {
        $facts[] = ['icon' => 'bed', 'text' => $unit['bed_type'] . ' bed'];
    }
    $facts[] = ['icon' => 'users', 'text' => 'Up to ' . (int)($unit['capacity_max'] ?? 2) . ' pax'];

    return $facts;
}

/**
 * Brand emblem, kept as a separate brand asset rather than an interface icon.
 */
function logo_mark(string $class = '', int $size = 0): string
{
    $classes = trim('logo-mark ' . $class);
    $dims    = $size > 0 ? ' width="' . $size . '" height="' . $size . '"' : '';

    return '<svg class="' . $classes . '"' . $dims . ' viewBox="0 0 100 100" role="img" aria-label="Flynn\'s Cozy Staycation">'
        . '<circle cx="50" cy="45" r="40" fill="currentColor"/>'
        . '<path d="M68 4.5v18" stroke="var(--mark-cut, #f2efe4)" stroke-width="3" stroke-linecap="round"/>'
        . '<circle cx="68" cy="31" r="8.4" fill="var(--mark-cut, #f2efe4)"/>'
        . '<path d="M39 42c-5 0-9 4-9 9v10.5c0 5.8 4.7 10.5 10.5 10.5H65c5.5 0 10-4.5 10-10v-.8c0-3.7-3-6.7-6.7-6.7H57.2c-1.7 0-3-1.3-3-3v-1.5c0-5-4-8-9-8Z" fill="var(--mark-cut, #f2efe4)"/>'
        . '<path d="M45 48v9.5c0 3.3 2.7 6 6 6h12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>'
        . '<path d="M54 63.5h14a3.6 3.6 0 0 1 0 7.2H54" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>'
        . '<path d="M41 75.5v4M56 75.5v4M70 75.5v4" stroke="var(--mark-cut, #f2efe4)" stroke-width="3" stroke-linecap="round"/>'
        . '<path d="M8 82h84" stroke="var(--mark-cut, #f2efe4)" stroke-width="3" stroke-linecap="round"/>'
        . '<circle cx="4.5" cy="82" r="2" fill="var(--mark-cut, #f2efe4)"/>'
        . '<circle cx="95.5" cy="82" r="2" fill="var(--mark-cut, #f2efe4)"/>'
        . '</svg>';
}

/** Mark + two-line wordmark used in the header, footer and CMS. */
function logo_lockup(string $class = '', int $size = 42): string
{
    $brand = setting('brand_name', APP_NAME);

    return '<span class="brand-lockup ' . $class . '">'
        . logo_mark('brand-lockup-mark', $size)
        . '<span class="brand-lockup-text">'
        . '<span class="brand-lockup-name">' . e($brand) . '</span>'
        . '<span class="brand-lockup-tag">Airbnb / Transient</span>'
        . '</span>'
        . '</span>';
}
