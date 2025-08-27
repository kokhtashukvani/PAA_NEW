<?php
// /api/routes/forms.php

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
$id = $route_parts[1] ?? null;

switch ($method) {
    case 'GET':
        if ($id) {
            // Get a single form with all its fields
            $stmt_form = $pdo->prepare("SELECT * FROM forms WHERE id = ?");
            $stmt_form->execute([$id]);
            $form = $stmt_form->fetch();

            if (!$form) {
                json_response(['error' => 'Form not found'], 404);
            }

            $stmt_fields = $pdo->prepare("SELECT * FROM form_fields WHERE form_id = ? ORDER BY sort_order");
            $stmt_fields->execute([$id]);
            $fields = $stmt_fields->fetchAll();

            $form['fields'] = $fields;
            json_response($form);
        } else {
            // Get all forms (without fields, for a simple list)
            $stmt = $pdo->query("SELECT id, name, description FROM forms ORDER BY name");
            $forms = $stmt->fetchAll();
            json_response($forms);
        }
        break;

    case 'POST':
        $input = get_json_input();
        if (empty($input['name'])) {
            json_response(['error' => 'Form name is required.'], 400);
        }
        $sql = "INSERT INTO forms (name, description) VALUES (?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$input['name'], $input['description'] ?? null]);
        $new_id = $pdo->lastInsertId();

        $stmt = $pdo->prepare("SELECT * FROM forms WHERE id = ?");
        $stmt->execute([$new_id]);
        json_response($stmt->fetch(), 201);
        break;

    case 'PUT':
        if (!$id) json_response(['error' => 'Form ID required.'], 400);
        $input = get_json_input();
        $fields = $input['fields'] ?? [];

        try {
            $pdo->beginTransaction();

            // 1. Update form details
            $stmt_update_form = $pdo->prepare("UPDATE forms SET name = ?, description = ? WHERE id = ?");
            $stmt_update_form->execute([$input['name'], $input['description'], $id]);

            // 2. Get existing field IDs from DB for this form
            $stmt_get_ids = $pdo->prepare("SELECT id FROM form_fields WHERE form_id = ?");
            $stmt_get_ids->execute([$id]);
            $existing_field_ids = $stmt_get_ids->fetchAll(PDO::FETCH_COLUMN);

            $incoming_field_ids = [];

            // 3. Upsert (Update/Insert) incoming fields
            $stmt_update_field = $pdo->prepare("UPDATE form_fields SET label=?, name=?, type=?, options=?, is_required=?, sort_order=? WHERE id=?");
            $stmt_insert_field = $pdo->prepare("INSERT INTO form_fields (form_id, label, name, type, options, is_required, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)");

            foreach ($fields as $index => $field) {
                $sort_order = $index;
                $options_json = isset($field['options']) ? json_encode($field['options']) : null;

                if (isset($field['id']) && in_array($field['id'], $existing_field_ids)) {
                    // This is an existing field, UPDATE it
                    $incoming_field_ids[] = $field['id'];
                    $stmt_update_field->execute([$field['label'], $field['name'], $field['type'], $options_json, $field['is_required'], $sort_order, $field['id']]);
                } else {
                    // This is a new field, INSERT it
                    $stmt_insert_field->execute([$id, $field['label'], $field['name'], $field['type'], $options_json, $field['is_required'], $sort_order]);
                }
            }

            // 4. Delete fields that are in DB but not in the incoming array
            $fields_to_delete = array_diff($existing_field_ids, $incoming_field_ids);
            if (!empty($fields_to_delete)) {
                $placeholders = implode(',', array_fill(0, count($fields_to_delete), '?'));
                $stmt_delete = $pdo->prepare("DELETE FROM form_fields WHERE id IN ($placeholders)");
                $stmt_delete->execute($fields_to_delete);
            }

            $pdo->commit();
            json_response(['message' => 'Form updated successfully']);

        } catch (Exception $e) {
            $pdo->rollBack();
            json_response(['error' => 'Failed to update form: ' . $e->getMessage()], 500);
        }
        break;

    case 'DELETE':
        if (!$id) json_response(['error' => 'Form ID required.'], 400);
        $stmt = $pdo->prepare("DELETE FROM forms WHERE id = ?"); // Relies on ON DELETE CASCADE for fields
        $stmt->execute([$id]);
        http_response_code(204);
        exit();
        break;

    default:
        json_response(['error' => 'Method not allowed'], 405);
        break;
}
?>
