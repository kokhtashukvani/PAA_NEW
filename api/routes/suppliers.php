<?php
// /api/routes/suppliers.php

// This file handles all CRUD operations for suppliers.

// --- Authorization: Admin Only ---
try {
    $decoded_token = get_decoded_token();
    if ($decoded_token->role !== 'admin') {
        json_response(['error' => 'Forbidden: You do not have permission to access this resource.'], 403);
    }
} catch (Exception $e) {
    json_response(['error' => 'Unauthorized: ' . $e->getMessage()], 401);
}

$method = $_SERVER['REQUEST_METHOD'];
// The router in index.php gives us the route parts.
// e.g., /api/suppliers/1 -> $route_parts = ['suppliers', '1']
$id = $route_parts[1] ?? null;

switch ($method) {
    case 'GET':
        if ($id) {
            // Get a single supplier
            $stmt = $pdo->prepare("SELECT * FROM suppliers WHERE id = ?");
            $stmt->execute([$id]);
            $supplier = $stmt->fetch();
            if ($supplier) {
                json_response($supplier);
            } else {
                json_response(['error' => 'Supplier not found'], 404);
            }
        } else {
            // Get all suppliers
            $stmt = $pdo->query("SELECT * FROM suppliers ORDER BY name");
            $suppliers = $stmt->fetchAll();
            json_response($suppliers);
        }
        break;

    case 'POST':
        $input = get_json_input();
        if (empty($input['name'])) {
            json_response(['error' => 'Supplier name is required.'], 400);
        }

        $sql = "INSERT INTO suppliers (name, contact_person, email, phone, address) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $input['name'],
            $input['contact_person'] ?? null,
            $input['email'] ?? null,
            $input['phone'] ?? null,
            $input['address'] ?? null,
        ]);
        $new_id = $pdo->lastInsertId();

        // Fetch and return the newly created supplier
        $stmt = $pdo->prepare("SELECT * FROM suppliers WHERE id = ?");
        $stmt->execute([$new_id]);
        json_response($stmt->fetch(), 201);
        break;

    case 'PUT':
        if (!$id) {
            json_response(['error' => 'Supplier ID is required for update.'], 400);
        }
        $input = get_json_input();

        $sql = "UPDATE suppliers SET name = ?, contact_person = ?, email = ?, phone = ?, address = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $input['name'] ?? null,
            $input['contact_person'] ?? null,
            $input['email'] ?? null,
            $input['phone'] ?? null,
            $input['address'] ?? null,
            $id
        ]);

        // Fetch and return the updated supplier
        $stmt = $pdo->prepare("SELECT * FROM suppliers WHERE id = ?");
        $stmt->execute([$id]);
        json_response($stmt->fetch());
        break;

    case 'DELETE':
        if (!$id) {
            json_response(['error' => 'Supplier ID is required for deletion.'], 400);
        }
        $stmt = $pdo->prepare("DELETE FROM suppliers WHERE id = ?");
        $stmt->execute([$id]);

        // Return 204 No Content on successful deletion
        http_response_code(204);
        exit();
        break;

    default:
        json_response(['error' => 'Method not allowed'], 405);
        break;
}
?>
