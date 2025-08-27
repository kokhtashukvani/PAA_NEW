<?php
// scripts/seed_default_form.php
// Seeds the database with a default purchase request form.

require_once __DIR__ . '/../api/db.php'; // Reuse the database connection

$form_name = 'فرم درخواست خرید';

try {
    // Check if the form already exists
    $stmt = $pdo->prepare("SELECT id FROM forms WHERE name = ?");
    $stmt->execute([$form_name]);
    if ($stmt->fetch()) {
        echo "Default form already exists.\n";
        exit(0);
    }

    $pdo->beginTransaction();

    // 1. Insert the form
    $stmt_form = $pdo->prepare("INSERT INTO forms (name, description) VALUES (?, ?)");
    $stmt_form->execute([$form_name, 'برای ثبت درخواست‌های خرید کالا یا خدمات استفاده می‌شود.']);
    $form_id = $pdo->lastInsertId();

    // 2. Define the fields for this form
    $fields = [
        ['label' => 'نام کالا / خدمات', 'name' => 'item_name', 'type' => 'text', 'is_required' => 1, 'sort_order' => 0],
        ['label' => 'تعداد', 'name' => 'quantity', 'type' => 'number', 'is_required' => 1, 'sort_order' => 1],
        ['label' => 'واحد', 'name' => 'unit', 'type' => 'text', 'is_required' => 0, 'sort_order' => 2, 'placeholder' => 'عدد، کیلوگرم، بسته'],
        ['label' => 'توضیحات تکمیلی', 'name' => 'description', 'type' => 'textarea', 'is_required' => 0, 'sort_order' => 3],
        ['label' => 'تاریخ نیاز', 'name' => 'required_date', 'type' => 'date', 'is_required' => 0, 'sort_order' => 4],
    ];

    // 3. Insert the fields
    $stmt_field = $pdo->prepare(
        "INSERT INTO form_fields (form_id, label, name, type, is_required, sort_order)
         VALUES (?, ?, ?, ?, ?, ?)"
    );

    foreach ($fields as $field) {
        $stmt_field->execute([
            $form_id,
            $field['label'],
            $field['name'],
            $field['type'],
            $field['is_required'],
            $field['sort_order']
        ]);
    }

    $pdo->commit();

    echo "Default purchase request form and fields seeded successfully!\n";
    echo "The new form has ID: " . $form_id . ". The frontend uses this ID (1) by default.\n";

} catch (Exception $e) {
    $pdo->rollBack();
    echo "Failed to seed default form: " . $e->getMessage() . "\n";
    exit(1);
}
?>
