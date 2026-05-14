<?php

declare(strict_types=1);

require __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Invalid request method.');
}

$input = request_body();
$fullName = trim((string) ($input['full_name'] ?? ''));
$username = trim((string) ($input['username'] ?? ''));
$email = trim((string) ($input['email'] ?? ''));
$password = (string) ($input['password'] ?? '');
$confirmPassword = (string) ($input['confirm_password'] ?? '');

if ($fullName === '' || $username === '' || $email === '' || $password === '' || $confirmPassword === '') {
    json_response(false, 'All fields are required.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_response(false, 'Please enter a valid email address.');
}

if ($password !== $confirmPassword) {
    json_response(false, 'Passwords do not match.');
}

if (strlen($password) < 6) {
    json_response(false, 'Password must be at least 6 characters long.');
}

try {
    $pdo = mysql_connection();

    $check = $pdo->prepare('SELECT id FROM users WHERE username = :username OR email = :email LIMIT 1');
    $check->execute([
        ':username' => $username,
        ':email' => $email,
    ]);

    if ($check->fetch()) {
        json_response(false, 'Username or email already exists.');
    }

    $insert = $pdo->prepare('INSERT INTO users (full_name, username, email, password_hash) VALUES (:full_name, :username, :email, :password_hash)');
    $insert->execute([
        ':full_name' => $fullName,
        ':username' => $username,
        ':email' => $email,
        ':password_hash' => password_hash($password, PASSWORD_DEFAULT),
    ]);

    $userId = (int) $pdo->lastInsertId();

    $profile = [
        'user_id' => $userId,
        'age' => null,
        'dob' => null,
        'contact' => '',
        'city' => '',
        'address' => '',
        'bio' => '',
        'created_at' => date('c'),
        'updated_at' => date('c'),
    ];

    $bulk = new MongoDB\Driver\BulkWrite();
    $bulk->insert($profile);
    mongo_manager()->executeBulkWrite(MONGO_DB . '.' . MONGO_COLLECTION, $bulk);

    json_response(true, 'Registration successful. Please log in.');
} catch (Throwable $throwable) {
    json_response(false, 'Registration failed: ' . $throwable->getMessage());
}
