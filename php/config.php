<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

const MYSQL_HOST = '127.0.0.1';
const MYSQL_PORT = '3306';
const MYSQL_DB = 'sem4_lab';
const MYSQL_USER = 'root';
const MYSQL_PASSWORD = '';

const MONGO_URI = 'mongodb://127.0.0.1:27017';
const MONGO_DB = 'sem4_lab';
const MONGO_COLLECTION = 'profiles';

const REDIS_HOST = '127.0.0.1';
const REDIS_PORT = 6379;
const REDIS_TTL = 86400;

function json_response(bool $success, string $message, array $data = []): void
{
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data,
    ]);
    exit;
}

function mysql_connection(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', MYSQL_HOST, MYSQL_PORT, MYSQL_DB);
    $pdo = new PDO($dsn, MYSQL_USER, MYSQL_PASSWORD, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $pdo;
}

function mongo_manager(): MongoDB\Driver\Manager
{
    static $manager = null;

    if ($manager instanceof MongoDB\Driver\Manager) {
        return $manager;
    }

    $manager = new MongoDB\Driver\Manager(MONGO_URI);
    return $manager;
}

function redis_client(): Redis
{
    static $redis = null;

    if ($redis instanceof Redis) {
        return $redis;
    }

    $redis = new Redis();
    $redis->connect(REDIS_HOST, REDIS_PORT, 2.5);

    return $redis;
}

function request_body(): array
{
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

    if (stripos($contentType, 'application/json') !== false) {
        $payload = json_decode(file_get_contents('php://input') ?: '[]', true);
        return is_array($payload) ? $payload : [];
    }

    return $_POST;
}

function generate_token(): string
{
    return bin2hex(random_bytes(32));
}

function get_session_from_token(string $token): ?array
{
    $stored = redis_client()->get('session:' . $token);

    if (!$stored) {
        return null;
    }

    $decoded = json_decode($stored, true);
    return is_array($decoded) ? $decoded : null;
}

function get_profile_document(int $userId): array
{
    $query = new MongoDB\Driver\Query(['user_id' => $userId], ['limit' => 1]);
    $cursor = mongo_manager()->executeQuery(MONGO_DB . '.' . MONGO_COLLECTION, $query);
    $documents = $cursor->toArray();

    if (empty($documents)) {
        return [];
    }

    return json_decode(json_encode($documents[0], JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
}
