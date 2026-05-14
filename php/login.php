<?php

declare(strict_types=1);

require __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Invalid request method.');
}

$input = request_body();
$identifier = trim((string) ($input['identifier'] ?? ''));
$password = (string) ($input['password'] ?? '');

if ($identifier === '' || $password === '') {
    json_response(false, 'Username/email and password are required.');
}

try {
    $pdo = mysql_connection();
    $statement = $pdo->prepare('SELECT id, full_name, username, email, password_hash FROM users WHERE username = :identifier OR email = :identifier LIMIT 1');
    $statement->execute([':identifier' => $identifier]);
    $user = $statement->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        json_response(false, 'Invalid login details.');
    }

    $token = generate_token();
    $sessionPayload = json_encode([
        'user_id' => (int) $user['id'],
        'full_name' => $user['full_name'],
        'username' => $user['username'],
        'email' => $user['email'],
    ], JSON_THROW_ON_ERROR);

    redis_client()->setex('session:' . $token, REDIS_TTL, $sessionPayload);

    json_response(true, 'Login successful.', [
        'token' => $token,
        'user' => [
            'id' => (int) $user['id'],
            'full_name' => $user['full_name'],
            'username' => $user['username'],
            'email' => $user['email'],
        ],
    ]);
} catch (Throwable $throwable) {
    json_response(false, 'Login failed: ' . $throwable->getMessage());
}
