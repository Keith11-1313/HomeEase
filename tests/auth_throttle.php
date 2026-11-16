<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/auth.php';

$_SESSION['login_attempts'] = ['started' => time(), 'count' => 4];
assert(login_throttled() === false);
$_SESSION['login_attempts']['count'] = 5;
assert(login_throttled() === true);
$_SESSION['login_attempts']['started'] = time() - 901;
assert(login_throttled() === false);
assert($_SESSION['login_attempts']['count'] === 0);

echo "Login throttle checks passed.\n";
