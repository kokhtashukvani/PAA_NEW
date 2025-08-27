<?php
// /api/routes/login.php

use Firebase\JWT\JWT;

// Only allow POST requests.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Method not allowed'], 405);
}

$input = get_json_input();
$username = $input['username'] ?? null;
$password = $input['password'] ?? null;

if (empty($username) || empty($password)) {
    json_response(['error' => 'Username and password are required.'], 400);
}

// --- Find User and Verify Password ---
try {
    $stmt = $pdo->prepare("SELECT id, username, password_hash, role FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    // Verify user exists and password is correct.
    if (!$user || !password_verify($password, $user['password_hash'])) {
        json_response(['error' => 'Invalid credentials.'], 401);
    }

    // --- Generate JWT ---
    // In a real app, this key should be stored securely in an environment variable.
    $secret_key = 'YOUR_SUPER_SECRET_KEY_CHANGE_ME';
    $issued_at = time();
    $expiration_time = $issued_at + (60 * 60 * 24); // Token valid for 24 hours
    $issuer = 'http://localhost'; // Should be your domain

    $payload = [
        'iss' => $issuer,
        'iat' => $issued_at,
        'exp' => $expiration_time,
        'sub' => $user['id'], // Subject of the token (user ID)
        'role' => $user['role'],
    ];

    $jwt = JWT::encode($payload, $secret_key, 'HS256');

    json_response([
        'message' => 'Login successful.',
        'token' => $jwt,
        'user' => [
            'id' => $user['id'],
            'username' => $user['username'],
            'role' => $user['role']
        ]
    ], 200);

} catch (PDOException $e) {
    json_response(['error' => 'Database error during login.'], 500);
} catch (Exception $e) {
    json_response(['error' => 'Failed to generate token.'], 500);
}
?>
