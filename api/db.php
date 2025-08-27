<?php
// /api/db.php - Database Connection

$db_file = __DIR__ . '/../purchasing.sqlite';
$dsn = 'sqlite:' . $db_file;

try {
    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Optional: Set default fetch mode to associative array
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // If the connection fails, we can't do anything.
    // In a real app, you'd log this error.
    // For now, we'll just stop execution and show a generic error.
    // The main index.php might not have loaded helpers yet, so use raw json.
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed.']);
    exit();
}
?>
