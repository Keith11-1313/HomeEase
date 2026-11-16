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
        'units'      => (int)db()->query('SELECT COUNT(*) FROM units')->fetchColumn(),
        'published'  => (int)db()->query('SELECT COUNT(*) FROM units WHERE is_published')->fetchColumn(),
        'photos'     => (int)db()->query('SELECT COUNT(*) FROM unit_photos')->fetchColumn(),
        'faqs'       => (int)db()->query('SELECT COUNT(*) FROM faqs')->fetchColumn(),
        'amenities'  => (int)db()->query('SELECT COUNT(*) FROM amenities')->fetchColumn(),
    ];
}
