<?php
// /api/helpers.php - Helper Functions

/**
 * Sends a JSON response with a specified HTTP status code.
 *
 * @param mixed $data The data to encode as JSON.
 * @param int $status_code The HTTP status code to set.
 */
function json_response($data, $status_code = 200) {
    // Set the content type header to application/json
    header('Content-Type: application/json');
    // Set the HTTP response code
    http_response_code($status_code);
    // Encode the data to JSON and echo it
    echo json_encode($data);
    // Terminate the script
    exit();
}

/**
 * Gets the JSON input from the request body.
 *
 * @return mixed The decoded JSON data as an associative array or null if invalid.
 */
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

function get_json_input() {
    return json_decode(file_get_contents('php://input'), true);
}

/**
 * Validates the JWT from the Authorization header and returns the decoded token data.
 *
 * @return object The decoded JWT payload.
 * @throws Exception If the token is invalid, expired, or not provided.
 */
function get_decoded_token() {
    // Get the authorization header
    $auth_header = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
    if (!$auth_header) {
        throw new Exception('Authorization header not found.');
    }

    // The header is expected to be in the format "Bearer <token>"
    $parts = explode(' ', $auth_header);
    if (count($parts) !== 2 || $parts[0] !== 'Bearer') {
        throw new Exception('Invalid token format.');
    }
    $jwt = $parts[1];

    // In a real app, this key should be stored securely in an environment variable.
    $secret_key = 'YOUR_SUPER_SECRET_KEY_CHANGE_ME';

    try {
        $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));
        return $decoded;
    } catch (ExpiredException $e) {
        throw new Exception('Token has expired.');
    } catch (Exception $e) {
        // This catches other errors like signature invalid
        throw new Exception('Invalid token: ' . $e->getMessage());
    }
}

?>
