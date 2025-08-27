<?php
// install.php
// A comprehensive installer script for the Purchasing Assistant application.

// --- Helper Functions ---
function print_header($title) {
    echo "\n--------------------------------------------------\n";
    echo "--- " . $title . "\n";
    echo "--------------------------------------------------\n";
}

function print_success($message) {
    echo "[SUCCESS] " . $message . "\n";
}

function print_info($message) {
    echo "[INFO]    " . $message . "\n";
}

function print_error($message) {
    echo "[ERROR]   " . $message . "\n";
}

// --- 1. Database Setup ---
print_header("Setting up database...");
try {
    $db_file = __DIR__ . '/purchasing.sqlite';
    $dsn = 'sqlite:' . $db_file;
    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql_schema = "
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT, username TEXT NOT NULL UNIQUE, password_hash TEXT NOT NULL,
            role TEXT NOT NULL CHECK(role IN ('admin', 'buyer')) DEFAULT 'buyer', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        CREATE TABLE IF NOT EXISTS suppliers (
            id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL, contact_person TEXT, email TEXT UNIQUE,
            phone TEXT, address TEXT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        CREATE TABLE IF NOT EXISTS forms (
            id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL UNIQUE, description TEXT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        CREATE TABLE IF NOT EXISTS form_fields (
            id INTEGER PRIMARY KEY AUTOINCREMENT, form_id INTEGER NOT NULL, label TEXT NOT NULL, name TEXT NOT NULL,
            type TEXT NOT NULL CHECK(type IN ('text', 'textarea', 'number', 'date', 'select')),
            options TEXT, is_required BOOLEAN NOT NULL DEFAULT 0, sort_order INTEGER DEFAULT 0,
            FOREIGN KEY (form_id) REFERENCES forms(id) ON DELETE CASCADE
        );
        CREATE TABLE IF NOT EXISTS purchase_requests (
            id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NOT NULL, form_id INTEGER NOT NULL,
            status TEXT NOT NULL CHECK(status IN ('pending', 'approved', 'rejected')) DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id), FOREIGN KEY (form_id) REFERENCES forms(id)
        );
        CREATE TABLE IF NOT EXISTS purchase_request_data (
            id INTEGER PRIMARY KEY AUTOINCREMENT, request_id INTEGER NOT NULL, field_name TEXT NOT NULL, field_value TEXT,
            FOREIGN KEY (request_id) REFERENCES purchase_requests(id) ON DELETE CASCADE
        );
    ";
    $pdo->exec($sql_schema);
    print_success("Database schema created successfully at 'purchasing.sqlite'.");
} catch (PDOException $e) {
    print_error("Database setup failed: " . $e->getMessage());
    exit(1);
}

// --- 2. Create Admin User ---
print_header("Creating admin user...");
try {
    $username = 'admin';
    $password = 'password123';
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        print_info("Admin user already exists.");
    } else {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt_insert = $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, 'admin')");
        $stmt_insert->execute([$username, $password_hash]);
        print_success("Admin user created. Username: $username, Password: $password");
    }
} catch (PDOException $e) {
    print_error("Failed to create admin user: " . $e->getMessage());
    exit(1);
}

// --- 3. Seed Default Form ---
print_header("Seeding default purchase request form...");
try {
    $form_name = 'فرم درخواست خرید';
    $stmt = $pdo->prepare("SELECT id FROM forms WHERE name = ?");
    $stmt->execute([$form_name]);
    if ($stmt->fetch()) {
        print_info("Default form already exists.");
    } else {
        $pdo->beginTransaction();
        $stmt_form = $pdo->prepare("INSERT INTO forms (name, description) VALUES (?, ?)");
        $stmt_form->execute([$form_name, 'برای ثبت درخواست‌های خرید کالا یا خدمات استفاده می‌شود.']);
        $form_id = $pdo->lastInsertId();

        $fields = [
            ['label' => 'نام کالا / خدمات', 'name' => 'item_name', 'type' => 'text', 'is_required' => 1, 'sort_order' => 0],
            ['label' => 'تعداد', 'name' => 'quantity', 'type' => 'number', 'is_required' => 1, 'sort_order' => 1],
            ['label' => 'واحد', 'name' => 'unit', 'type' => 'text', 'is_required' => 0, 'sort_order' => 2],
            ['label' => 'توضیحات تکمیلی', 'name' => 'description', 'type' => 'textarea', 'is_required' => 0, 'sort_order' => 3],
        ];
        $stmt_field = $pdo->prepare("INSERT INTO form_fields (form_id, label, name, type, is_required, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($fields as $field) {
            $stmt_field->execute([$form_id, $field['label'], $field['name'], $field['type'], $field['is_required'], $field['sort_order']]);
        }
        $pdo->commit();
        print_success("Default purchase request form (ID: $form_id) seeded successfully.");
    }
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    print_error("Failed to seed default form: " . $e->getMessage());
    exit(1);
}

// --- 4. Frontend Dependencies ---
print_header("Installing frontend dependencies (npm)...");
$frontend_dir = __DIR__ . '/frontend';
if (is_dir($frontend_dir)) {
    print_info("Found 'frontend' directory. Running 'npm install'...");
    // Use shell_exec to capture output, and echo it.
    // The '2>&1' part redirects stderr to stdout to capture errors.
    $npm_output = shell_exec("cd " . escapeshellarg($frontend_dir) . " && npm install 2>&1");
    echo $npm_output;
    if (strpos($npm_output, 'added') !== false || strpos($npm_output, 'up to date') !== false) {
        print_success("Frontend dependencies installed successfully.");
    } else {
        print_error("NPM install might have failed. Please check the output above.");
    }
} else {
    print_error("'frontend' directory not found. Skipping npm install.");
}


print_header("Setup Complete!");
?>
