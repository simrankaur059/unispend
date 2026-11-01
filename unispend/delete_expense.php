<?php
declare(strict_types=1);

require __DIR__ . '/includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Use the expense history page to delete an expense.');
}

$expenseId = filter_input(INPUT_POST, 'expense_id', FILTER_VALIDATE_INT);
if (!$expenseId) {
    http_response_code(400);
    exit('A valid expense record is required.');
}

$statement = $pdo->prepare(
    'DELETE FROM expenses WHERE expense_id = :expense_id AND user_id = :user_id'
);
$statement->execute(['expense_id' => $expenseId, 'user_id' => 1]);

header('Location: expenses.php?deleted=1');
exit;
