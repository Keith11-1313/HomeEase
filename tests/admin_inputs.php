<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/bootstrap.php';

assert(drive_video_id('https://drive.google.com/file/d/abc_123-XY/view?usp=sharing') === 'abc_123-XY');
assert(drive_video_id('https://drive.google.com/open?id=abc_123-XY') === 'abc_123-XY');
assert(drive_video_id('https://drive.google.com.evil.test/file/d/abc') === null);
assert(drive_video_id('http://drive.google.com/file/d/abc/view') === null);
assert(drive_video_id('https://example.com/file/d/abc') === null);

echo "Admin input checks passed.\n";
