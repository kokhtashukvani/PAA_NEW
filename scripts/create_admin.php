<?php
// scripts/create_admin.php
// A simple command-line script to create an admin user.

require_once __DIR__ . '/../api/db.php'; // Reuse the database connection

$username = 'admin';
$password = 'password123'; // Use a simple password for setup

// Check if admin already exists
$stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
$stmt->execute([$username]);
if ($stmt->fetch()) {
    echo "Admin user already exists.\n";
    exit(0);
}

// Hash the password
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Insert the admin user
try {
    $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, 'admin')");
    $stmt->execute([$username, $password_hash]);
    echo "Admin user created successfully!\n";
    echo "Username: " . $username . "\n";
    echo "Password: " . $password . "\n";
} catch (PDOException $e) {
    echo "Failed to create admin user: " . $e->getMessage() . "\n";
    exit(1);
}
?>
