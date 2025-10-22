<?php
$db = require __DIR__ . '/db.php';
// test database! Important not to run tests on production or development databases
// Use the same database as dev but ensure tests clean up after themselves
// In Docker environment, the host is 'db' service name
$db['dsn'] = sprintf(
    'mysql:host=%s;port=%s;dbname=%s',
    $_ENV['DB_HOST'] ?? 'db',
    $_ENV['DB_PORT'] ?? '3306',
    $_ENV['DB_DATABASE'] ?? 'book_catalog'
);

return $db;
