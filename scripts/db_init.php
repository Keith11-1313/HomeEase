<?php
declare(strict_types=1);

/**
 * Load the schema and seed data. Both files are idempotent,
 * so running this again is safe.
 *
 *   php scripts/db_init.php
 */

require_once __DIR__ . '/../lib/bootstrap.php';

foreach (['db/schema.sql', 'db/seed.sql'] as $file) {
    $path = __DIR__ . '/../' . $file;
    if (!is_file($path)) {
        fwrite(STDERR, "Missing {$file}\n");
        exit(1);
    }

    echo "Applying {$file}...\n";
    db()->exec((string)file_get_contents($path));
}

db()->prepare('UPDATE settings SET value = :value WHERE "key" = :key AND value = :old')
    ->execute(['key' => 'brand_name', 'old' => "Flynn's Rate", 'value' => "Flynn's Cozy Staycation"]);
db()->prepare('UPDATE settings SET value = :value WHERE "key" = :key AND value = :old')
    ->execute(['key' => 'messenger_url', 'old' => 'https://m.me/flynnsrate', 'value' => 'https://m.me/flynnscozystaycation']);

echo "Done.\n";
