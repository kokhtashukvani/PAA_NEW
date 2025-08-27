<?php
// /api/index.php - Main API Router

// --- Global Headers ---
// Allow requests from any origin (for development).
// In production, you would restrict this to your frontend's domain.
header("Access-Control-Allow-Origin: *");
// Allow common HTTP methods.
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
// Allow specific headers, including Authorization for JWT.
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Respond to preflight requests (OPTIONS method).
// This is a check that browsers perform before making the actual request.
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// --- Includes ---
// Composer's autoloader
require_once __DIR__ . '/vendor/autoload.php';

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

// --- Simple Routing ---
// Get the requested path from the URL.
$request_uri = $_SERVER['REQUEST_URI'];
$base_path = '/api'; // This should match the directory name.

// Remove the base path and query string from the URI.
$route = str_replace('?' . $_SERVER['QUERY_STRING'], '', $request_uri);
$route = str_replace($base_path, '', $route);
$route = trim($route, '/');

// Split the route into parts. e.g., 'users/1' becomes ['users', '1'].
$route_parts = explode('/', $route);
$resource = $route_parts[0] ?? null;

// Route the request to the corresponding resource file.
// e.g., a request to /api/users will load /api/routes/users.php
$resource_file = __DIR__ . '/routes/' . $resource . '.php';

if ($resource && file_exists($resource_file)) {
    require $resource_file;
} else {
    // Handle 404 Not Found
    json_response(['error' => 'Endpoint not found'], 404);
}

?>
