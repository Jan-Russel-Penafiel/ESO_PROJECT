<?php
// =====================================================
// Database connection (PDO, prepared statements only)
// =====================================================
require_once __DIR__ . '/config.php';

/**
 * Returns a singleton PDO instance.
 */
function db() {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            die('Database connection failed: ' . htmlspecialchars($e->getMessage()));
        }
    }
    return $pdo;
}

/**
 * Run a SELECT and return all rows.
 */
function db_all($sql, $params = []) {
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Run a SELECT and return one row (or false).
 */
function db_one($sql, $params = []) {
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch();
}

/**
 * Run an INSERT/UPDATE/DELETE and return affected rows.
 */
function db_exec($sql, $params = []) {
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->rowCount();
}

/**
 * Insert and return last insert id.
 */
function db_insert($sql, $params = []) {
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return (int) db()->lastInsertId();
}
