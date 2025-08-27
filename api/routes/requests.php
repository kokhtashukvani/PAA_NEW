<?php
// /api/routes/requests.php

// --- Authorization: Any authenticated user ---
try {
    $decoded_token = get_decoded_token();
    $user_id = $decoded_token->sub;
} catch (Exception $e) {
    json_response(['error' => 'Unauthorized: ' . $e->getMessage()], 401);
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Fetch all requests for the logged-in user
        $sql = "SELECT pr.id, pr.status, pr.created_at, f.name as form_name
                FROM purchase_requests pr
                JOIN forms f ON pr.form_id = f.id
                WHERE pr.user_id = ?
                ORDER BY pr.created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id]);
        $requests = $stmt->fetchAll();
        json_response($requests);
        break;

    case 'POST':
        // --- Handle Submission ---
        $input = get_json_input();
        $form_id = $input['form_id'] ?? null;
        $submission_data = $input['data'] ?? null;

        if (empty($form_id) || empty($submission_data) || !is_array($submission_data)) {
            json_response(['error' => 'Invalid input. form_id and data are required.'], 400);
        }

        try {
            $pdo->beginTransaction();

            // 1. Create a record in the main purchase_requests table
            $stmt_request = $pdo->prepare("INSERT INTO purchase_requests (user_id, form_id, status) VALUES (?, ?, 'pending')");
            $stmt_request->execute([$user_id, $form_id]);
            $request_id = $pdo->lastInsertId();

            // 2. Insert each piece of data into the purchase_request_data table
            $stmt_data = $pdo->prepare("INSERT INTO purchase_request_data (request_id, field_name, field_value) VALUES (?, ?, ?)");
            foreach ($submission_data as $field_name => $field_value) {
                $value_to_store = is_scalar($field_value) ? (string)$field_value : json_encode($field_value);
                $stmt_data->execute([$request_id, $field_name, $value_to_store]);
            }

            $pdo->commit();

            json_response([
                'message' => 'Purchase request submitted successfully.',
                'request_id' => $request_id
            ], 201);

        } catch (Exception $e) {
            $pdo->rollBack();
            json_response(['error' => 'Failed to submit request: ' . $e->getMessage()], 500);
        }
        break;

    default:
        json_response(['error' => 'Method not allowed'], 405);
        break;
}
?>
