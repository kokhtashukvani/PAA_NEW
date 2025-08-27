<?php
// database.php

// --- Configuration ---
// Use an absolute path to ensure it works regardless of where the script is called from.
$db_file = __DIR__ . '/purchasing.sqlite';
$dsn = 'sqlite:' . $db_file;

// --- Main Execution ---
try {
    // Create (connect to) the database.
    $pdo = new PDO($dsn);
    // Set PDO to throw exceptions on error.
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Successfully connected to the database: " . realpath($db_file) . "\n";

    // --- Schema Definition ---
    // The following is a single multi-statement string to create all tables.
    $sql = "
        -- Users Table: Stores user accounts and roles
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            password_hash TEXT NOT NULL,
            role TEXT NOT NULL CHECK(role IN ('admin', 'buyer')) DEFAULT 'buyer',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );

        -- Suppliers Table: Stores supplier information (Admin managed)
        CREATE TABLE IF NOT EXISTS suppliers (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            contact_person TEXT,
            email TEXT UNIQUE,
            phone TEXT,
            address TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );

        -- Forms Table: Defines the structure of a form
        CREATE TABLE IF NOT EXISTS forms (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL UNIQUE,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );

        -- Form Fields Table: Defines individual fields within a form
        CREATE TABLE IF NOT EXISTS form_fields (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            form_id INTEGER NOT NULL,
            label TEXT NOT NULL,       -- The human-readable label (e.g., 'Product Name')
            name TEXT NOT NULL,        -- The machine-readable name (e.g., 'product_name')
            type TEXT NOT NULL CHECK(type IN ('text', 'textarea', 'number', 'date', 'select')),
            options TEXT,              -- JSON encoded options for 'select' type
            is_required BOOLEAN NOT NULL DEFAULT 0,
            sort_order INTEGER DEFAULT 0,
            FOREIGN KEY (form_id) REFERENCES forms(id) ON DELETE CASCADE
        );

        -- Purchase Requests Table: A specific submission of a form by a user
        CREATE TABLE IF NOT EXISTS purchase_requests (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            form_id INTEGER NOT NULL,
            status TEXT NOT NULL CHECK(status IN ('pending', 'approved', 'rejected')) DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id),
            FOREIGN KEY (form_id) REFERENCES forms(id)
        );

        -- Purchase Request Data Table: Stores the actual data for each field in a submission
        CREATE TABLE IF NOT EXISTS purchase_request_data (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            request_id INTEGER NOT NULL,
            field_name TEXT NOT NULL,  -- Corresponds to form_fields.name
            field_value TEXT,
            FOREIGN KEY (request_id) REFERENCES purchase_requests(id) ON DELETE CASCADE
        );
    ";

    // Execute the SQL to create the tables.
    $pdo->exec($sql);

    echo "All tables created successfully or already exist.\n";

} catch (PDOException $e) {
    // Catch and display any errors.
    echo "Database error: " . $e->getMessage() . "\n";
    // Exit with an error code.
    exit(1);
}

?>
