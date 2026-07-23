<?php
/**
 * Idempotent demo admin seed for empty Railway databases.
 * Creates login user if missing. Password stored as MD5 because login.js submits MD5 hashes.
 */

define('ROOT', dirname(__DIR__));
define('INCLUDE_PATH', ROOT . '/includes');
define('MODULES_PATH', ROOT . '/modules');

require INCLUDE_PATH . '/config.php';
require INCLUDE_PATH . '/lib.php';
require ROOT . '/finascop_config/config.php';

$username = getenv('SEED_ADMIN_USERNAME') ?: 'retaline';
$password = getenv('SEED_ADMIN_PASSWORD') ?: '123456';
$email = getenv('SEED_ADMIN_EMAIL') ?: 'admin@grozeo.com';
$firstName = getenv('SEED_ADMIN_FIRST_NAME') ?: 'Grozeo';
$lastName = getenv('SEED_ADMIN_LAST_NAME') ?: 'Admin';

$db = new sqlDb(DSN);

$existing = $db->getFromSafe(
    'SELECT UserId FROM ' . FINASCOP_DB . 'finascop_usr_master WHERE UserName = ?',
    's',
    [$username],
    true
);

if ($existing !== false && !empty($existing)) {
    echo "Seed skipped: user '{$username}' already exists (UserId {$existing['UserId']}).\n";
    exit(0);
}

$roleId = $db->getItemFromDB("SELECT RoleId FROM sys_role WHERE RoleName = 'Super User' LIMIT 1");
if (empty($roleId)) {
    $roleId = $db->getItemFromDB('SELECT RoleId FROM sys_role WHERE IsEnabled = "Yes" ORDER BY RoleId ASC LIMIT 1');
}
if (empty($roleId)) {
    $db->perform('sys_role', [
        'RoleName' => 'Super User',
        'IsEnabled' => 'Yes',
    ]);
    $roleId = $db->insert_id();
}

if (empty($roleId)) {
    fwrite(STDERR, "Seed failed: unable to resolve sys_role RoleId.\n");
    exit(1);
}

$passwordHash = md5($password);

$db->query('BEGIN');

$masterOk = $db->perform(FINASCOP_DB . 'finascop_usr_master', [
    'UserName' => $username,
    'Passwd' => $passwordHash,
    'UserEmail' => $email,
    'UserStatus' => 1,
    'IsActive' => 'Yes',
    'IsSuperUser' => 'Yes',
    'RoleId' => (int) $roleId,
    'JSCacheTime' => '',
]);

if (!$masterOk) {
    $db->query('ROLLBACK');
    fwrite(STDERR, "Seed failed: could not insert finascop_usr_master row.\n");
    exit(1);
}

$userId = (int) $db->insert_id();

$profileOk = $db->perform(FINASCOP_DB . 'finascop_usr_profile', [
    'UserId' => $userId,
    'FirstName' => $firstName,
    'LastName' => $lastName,
    'typId' => 1,
    'CreatedOn' => date('Y-m-d H:i:s'),
]);

if (!$profileOk) {
    $db->query('ROLLBACK');
    fwrite(STDERR, "Seed failed: could not insert finascop_usr_profile row.\n");
    exit(1);
}

$db->query('COMMIT');

echo "Seeded demo admin '{$username}' (UserId {$userId}).\n";
exit(0);
