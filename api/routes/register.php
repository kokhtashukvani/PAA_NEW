<?php
// /api/routes/register.php

// The main router script (`index.php`) has already included the autoloader,
// the database connection (`$pdo`), and helper functions.

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Only allow POST requests to this endpoint.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Method not allowed'], 405);
}

// Get the JSON input from the request body.
$input = get_json_input();
$username = $input['username'] ?? null;
$password = $input['password'] ?? null;

// --- Validation ---
if (empty($username) || empty($password)) {
    json_response(['error' => 'Username and password are required.'], 400);
}

// For simplicity, we'll just check password length.
// A real application should enforce more complex password policies.
if (strlen($password) < 8) {
    json_response(['error' => 'Password must be at least 8 characters long.'], 400);
}

// --- Check for existing user ---
try {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        json_response(['error' => 'Username already taken.'], 409); // 409 Conflict
    }
} catch (PDOException $e) {
    // In a real app, log this error.
    json_response(['error' => 'Database error during user check.'], 500);
}

// --- Create new user ---
// Hash the password for secure storage.
$password_hash = password_hash($password, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, 'buyer')");
    $stmt->execute([$username, $password_hash]);

    // Respond with a success message and 201 Created status.
    json_response([
        'message' => 'User registered successfully.',
        'user' => ['username' => $username]
    ], 201);

} catch (PDOException $e) {
    // In a real app, log this error.
    json_response(['error' => 'Failed to create user.'], 500);
}
?>
