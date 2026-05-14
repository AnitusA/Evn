<?php

declare(strict_types=1);

require __DIR__ . '/config.php';

$input = request_body();
$action = (string) ($_REQUEST['action'] ?? $input['action'] ?? 'get');
$token = trim((string) ($_REQUEST['token'] ?? $input['token'] ?? ''));

if ($token === '') {
    json_response(false, 'Session token is required.');
}

$session = get_session_from_token($token);

if (!$session) {
    json_response(false, 'Session expired or invalid.');
}

$userId = (int) ($session['user_id'] ?? 0);

if ($userId <= 0) {
    json_response(false, 'Invalid session data.');
}

try {
    if ($action === 'logout') {
        redis_client()->del('session:' . $token);
        json_response(true, 'Logged out successfully.');
    }

    if ($action === 'update') {
        $profileData = [
            'age' => $_POST['age'] ?? null,
            'dob' => trim((string) ($_POST['dob'] ?? '')),
            'contact' => trim((string) ($_POST['contact'] ?? '')),
            'city' => trim((string) ($_POST['city'] ?? '')),
            'address' => trim((string) ($_POST['address'] ?? '')),
            'bio' => trim((string) ($_POST['bio'] ?? '')),
            'updated_at' => date('c'),
        ];

        if ($profileData['age'] !== null && $profileData['age'] !== '') {
            $profileData['age'] = (int) $profileData['age'];
        } else {
            $profileData['age'] = null;
        }

        $bulk = new MongoDB\Driver\BulkWrite();
        $bulk->update(
            ['user_id' => $userId],
            ['$set' => $profileData],
            ['multi' => false, 'upsert' => true]
        );

        mongo_manager()->executeBulkWrite(MONGO_DB . '.' . MONGO_COLLECTION, $bulk);

        json_response(true, 'Profile updated successfully.');
    }

    $pdo = mysql_connection();
    $statement = $pdo->prepare('SELECT id, full_name, username, email FROM users WHERE id = :id LIMIT 1');
    $statement->execute([':id' => $userId]);
    $user = $statement->fetch();

    if (!$user) {
        json_response(false, 'User not found.');
    }

    $profile = get_profile_document($userId);

    json_response(true, 'Profile loaded.', [
        'id' => (int) $user['id'],
        'full_name' => $user['full_name'],
        'username' => $user['username'],
        'email' => $user['email'],
        'age' => $profile['age'] ?? '',
        'dob' => $profile['dob'] ?? '',
        'contact' => $profile['contact'] ?? '',
        'city' => $profile['city'] ?? '',
        'address' => $profile['address'] ?? '',
        'bio' => $profile['bio'] ?? '',
    ]);
} catch (Throwable $throwable) {
    json_response(false, 'Profile request failed: ' . $throwable->getMessage());
}
