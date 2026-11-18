<?php
declare(strict_types=1);

// ============================================================
// repo.php — every database read/write lives here
// ============================================================

// --- settings ------------------------------------------------
function settings_all(): array
{
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        foreach (db()->query('SELECT "key", value FROM settings') as $row) {
            $cache[$row['key']] = $row['value'];
        }
    }
    return $cache;
}

function setting(string $key, string $default = ''): string
{
    $all = settings_all();
    return isset($all[$key]) && $all[$key] !== '' ? $all[$key] : $default;
}

// --- units ---------------------------------------------------
function unit_select_sql(): string
{
    return 'SELECT u.*, b.name AS building_name,
                   (SELECT p.url FROM unit_photos p
                      WHERE p.unit_id = u.id
                        AND p.media_type = \'image\'
                     ORDER BY p.is_cover DESC, p.sort_order, p.id
                     LIMIT 1) AS cover_url,
                   (SELECT MIN(r.amount) FROM rates r WHERE r.unit_id = u.id) AS from_price,
                   (SELECT MAX(r.hours) FROM rates r WHERE r.unit_id = u.id) AS max_hours
            FROM units u
            LEFT JOIN buildings b ON b.id = u.building_id';
}

function units_published(): array
{
    return db()->query(unit_select_sql() . ' WHERE u.is_published ORDER BY u.sort_order, u.id')->fetchAll();
}

function units_all(): array
{
    return db()->query(unit_select_sql() . ' ORDER BY u.sort_order, u.id')->fetchAll();
}

function buildings_all(): array
{
    return db()->query('SELECT * FROM buildings ORDER BY name')->fetchAll();
}

function unit_by_slug(string $slug): ?array
{
    $stmt = db()->prepare(unit_select_sql() . ' WHERE u.slug = :slug LIMIT 1');
    $stmt->execute(['slug' => $slug]);
    return $stmt->fetch() ?: null;
}

function unit_by_id(int $id): ?array
{
    $stmt = db()->prepare(unit_select_sql() . ' WHERE u.id = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    return $stmt->fetch() ?: null;
}

// --- photos --------------------------------------------------
function unit_photos(int $unitId): array
{
    $stmt = db()->prepare('SELECT * FROM unit_photos WHERE unit_id = :id ORDER BY is_cover DESC, sort_order, id');
    $stmt->execute(['id' => $unitId]);
    return $stmt->fetchAll();
}

function gallery_photos(): array
{
    return db()->query(
        'SELECT p.*, u.name AS unit_name, u.slug AS unit_slug
           FROM unit_photos p
           JOIN units u ON u.id = p.unit_id
          WHERE u.is_published
          ORDER BY u.sort_order, p.media_type DESC, p.is_cover DESC, p.sort_order, p.id'
    )->fetchAll();
}

// --- inclusions ----------------------------------------------
function amenities_all(): array
{
    return db()->query('SELECT * FROM amenities ORDER BY category, sort_order, name')->fetchAll();
}

/** Grouped by category: ['Living' => [...], 'Media' => [...]] */
function unit_amenities(int $unitId): array
{
    $stmt = db()->prepare(
        'SELECT a.*, ua.note
           FROM unit_amenities ua
           JOIN amenities a ON a.id = ua.amenity_id
          WHERE ua.unit_id = :id
          ORDER BY a.category, a.sort_order, a.name'
    );
    $stmt->execute(['id' => $unitId]);

    $grouped = [];
    foreach ($stmt->fetchAll() as $row) {
        $grouped[$row['category']][] = $row;
    }
    return $grouped;
}

// --- pricing -------------------------------------------------
function unit_rates(int $unitId): array
{
    $stmt = db()->prepare('SELECT * FROM rates WHERE unit_id = :id ORDER BY hours, day_type, slot_type');
    $stmt->execute(['id' => $unitId]);
    return $stmt->fetchAll();
}

/** Cheapest published rate, for "from ₱…" copy. */
function price_from(): float
{
    $value = db()->query(
        'SELECT MIN(r.amount)
           FROM rates r
           JOIN units u ON u.id = r.unit_id
          WHERE u.is_published'
    )->fetchColumn();

    return $value === false || $value === null ? 0.0 : (float)$value;
}

function addons_all(bool $onlyActive = false): array
{
    $sql = 'SELECT * FROM addons' . ($onlyActive ? ' WHERE is_active' : '') . ' ORDER BY sort_order, label';
    return db()->query($sql)->fetchAll();
}

/** ['weekday' => 250.00, 'weekend_holiday' => 350.00] */
function pool_rates(int $unitId): array
{
    $stmt = db()->prepare('SELECT day_type, amount FROM pool_rates WHERE unit_id = :id');
    $stmt->execute(['id' => $unitId]);

    $map = [];
    foreach ($stmt->fetchAll() as $row) {
        $map[$row['day_type']] = (float)$row['amount'];
    }
    return $map;
}

function deposit_for(int $hours): ?array
{
    $stmt = db()->prepare(
        'SELECT amount, label FROM deposit_rules
          WHERE min_hours <= :hours
          ORDER BY min_hours DESC
          LIMIT 1'
    );
    $stmt->execute(['hours' => $hours]);
    return $stmt->fetch() ?: null;
}

function deposit_rules_all(): array
{
    return db()->query('SELECT * FROM deposit_rules ORDER BY min_hours')->fetchAll();
}

function day_type_for(string $date): string
{
    $day = new DateTimeImmutable($date);
    if ((int)$day->format('N') >= 6) {
        return 'weekend_holiday';
    }

    $stmt = db()->prepare('SELECT 1 FROM holidays WHERE holiday_date = :d');
    $stmt->execute(['d' => $day->format('Y-m-d')]);
    return $stmt->fetchColumn() ? 'weekend_holiday' : 'weekday';
}

// --- content -------------------------------------------------
function reviews_published(): array
{
    try {
        return db()->query('SELECT * FROM reviews WHERE is_published ORDER BY sort_order, review_date DESC NULLS LAST, id DESC')->fetchAll();
    } catch (Throwable) {
        return [];
    }
}

function reviews_all(): array
{
    try {
        return db()->query('SELECT * FROM reviews ORDER BY sort_order, created_at DESC')->fetchAll();
    } catch (Throwable) {
        return [];
    }
}

function standard_fees(): array
{
    static $fees = null;
    if ($fees !== null) {
        return $fees;
    }
    $addons = [];
    foreach (addons_all(true) as $a) {
        $addons[$a['code']] = (float)$a['amount'];
    }

    try {
        $weekendDiffs = db()->query(
            "SELECT r2.amount - r1.amount AS diff
             FROM rates r1
             JOIN rates r2 ON r1.unit_id = r2.unit_id AND r1.hours = r2.hours AND r1.slot_type = r2.slot_type
             WHERE r1.day_type = 'weekday' AND r2.day_type = 'weekend_holiday' AND r1.hours = 12"
        )->fetchAll(PDO::FETCH_COLUMN);
    } catch (Throwable) {
        $weekendDiffs = [];
    }

    $minDiff = !empty($weekendDiffs) ? min($weekendDiffs) : 50;
    $maxDiff = !empty($weekendDiffs) ? max($weekendDiffs) : 100;
    $surchargeStr = ($minDiff == $maxDiff) ? money($minDiff) : money($minDiff) . ' – ' . money($maxDiff);

    try {
        $poolAmounts = db()->query('SELECT amount FROM pool_rates')->fetchAll(PDO::FETCH_COLUMN);
    } catch (Throwable) {
        $poolAmounts = [];
    }
    $minPool = !empty($poolAmounts) ? min($poolAmounts) : 170;
    $maxPool = !empty($poolAmounts) ? max($poolAmounts) : 350;
    $poolStr = money($minPool) . ' – ' . money($maxPool);

    $fees = [
        'extra_pax'    => isset($addons['extra_pax']) ? money($addons['extra_pax']) . ' / pax' : '₱300 / pax',
        'extra_hour'   => isset($addons['extra_hour']) ? money($addons['extra_hour']) . ' / hour' : '₱180 / hour',
        'parking'      => '₱40 (pay) – ₱350 (reserved)',
        'pool'         => $poolStr,
        'weekend_diff' => '+' . $surchargeStr . ' per 12h',
    ];
    return $fees;
}

function faqs_published(): array
{
    return db()->query('SELECT * FROM faqs WHERE is_published ORDER BY sort_order, id')->fetchAll();
}

function random_gallery_images(int $n): array
{
    $stmt = db()->prepare(
        'SELECT p.url, p.caption, u.name AS unit_name, u.slug AS unit_slug
           FROM unit_photos p
           JOIN units u ON u.id = p.unit_id
          WHERE u.is_published AND p.media_type = \'image\'
          ORDER BY RANDOM()
          LIMIT :n'
    );
    $stmt->execute(['n' => $n]);
    return $stmt->fetchAll();
}

function faqs_all(): array
{
    return db()->query('SELECT * FROM faqs ORDER BY sort_order, id')->fetchAll();
}

// --- audit ---------------------------------------------------
function audit(string $action, string $table, ?string $recordId, ?array $old = null, ?array $new = null): void
{
    $stmt = db()->prepare(
        'INSERT INTO audit_logs (user_id, action, table_name, record_id, old_values, new_values)
         VALUES (:user, :action, :table, :record, :old, :new)'
    );
    $stmt->execute([
        'user'   => $_SESSION['admin']['id'] ?? null,
        'action' => $action,
        'table'  => $table,
        'record' => $recordId,
        'old'    => $old ? json_encode($old, JSON_UNESCAPED_UNICODE) : null,
        'new'    => $new ? json_encode($new, JSON_UNESCAPED_UNICODE) : null,
    ]);
}

function recent_audit(int $limit = 20): array
{
    $stmt = db()->prepare(
        'SELECT a.*, u.display_name
           FROM audit_logs a
           LEFT JOIN admin_users u ON u.id = a.user_id
          ORDER BY a.created_at DESC, a.id DESC
          LIMIT :limit'
    );
    $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

// --- dashboard counts ---------------------------------------
function dashboard_counts(): array
{
    return [
        'units'          => (int)db()->query('SELECT COUNT(*) FROM units')->fetchColumn(),
        'published'      => (int)db()->query('SELECT COUNT(*) FROM units WHERE is_published')->fetchColumn(),
        'photos'         => (int)db()->query('SELECT COUNT(*) FROM unit_photos')->fetchColumn(),
        'faqs'           => (int)db()->query('SELECT COUNT(*) FROM faqs WHERE is_published')->fetchColumn(),
        'amenities'      => (int)db()->query('SELECT COUNT(*) FROM amenities')->fetchColumn(),
        'no_photos'      => (int)db()->query('SELECT COUNT(*) FROM units WHERE is_published AND id NOT IN (SELECT DISTINCT unit_id FROM unit_photos WHERE media_type = \'image\')')->fetchColumn(),
        'no_cover'       => (int)db()->query('SELECT COUNT(*) FROM units WHERE is_published AND id NOT IN (SELECT unit_id FROM unit_photos WHERE is_cover AND media_type = \'image\')')->fetchColumn(),
        'blocked_soon'   => (int)db()->query('SELECT COUNT(*) FROM availability_blocks WHERE block_date BETWEEN CURRENT_DATE AND CURRENT_DATE + 14')->fetchColumn(),
        'announcements'  => (int)db()->query('SELECT COUNT(*) FROM announcements WHERE is_published')->fetchColumn(),
    ];
}
