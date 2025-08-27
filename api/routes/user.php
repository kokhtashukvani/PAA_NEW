<?php
// /api/routes/user.php

// This is a protected endpoint. It requires a valid JWT.

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(['error' => 'Method not allowed'], 405);
}

try {
    // The helper function will validate the token and throw an exception on failure.
    $decoded_token = get_decoded_token();
    $user_id = $decoded_token->sub;

    // Token is valid, so we can fetch the user's data from the database.
    $stmt = $pdo->prepare("SELECT id, username, role, created_at FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        // This case is unlikely if the token is valid, but it's good practice to check.
        json_response(['error' => 'User not found.'], 404);
    }

    // Return the user data.
    json_response($user, 200);

} catch (Exception $e) {
    // This will catch any exception thrown by get_decoded_token()
    json_response(['error' => 'Unauthorized: ' . $e->getMessage()], 401);
}
?>
