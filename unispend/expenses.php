<?php
declare(strict_types=1);

require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/functions.php';

$userId = 1;
$categories = $pdo->query(
    'SELECT category_id AS id, category_name AS name FROM categories ORDER BY category_name'
)->fetchAll();
$errors = [];
$editingExpense = null;
$formValues = [
    'expense_id' => '',
    'amount' => '',
    'category_id' => '',
    'expense_date' => date('Y-m-d'),
    'description' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    $formValues = [
        'expense_id' => trim((string) ($_POST['expense_id'] ?? '')),
        'amount' => trim((string) ($_POST['amount'] ?? '')),
        'category_id' => trim((string) ($_POST['category_id'] ?? '')),
        'expense_date' => trim((string) ($_POST['expense_date'] ?? '')),
        'description' => trim((string) ($_POST['description'] ?? '')),
    ];

    $amount = filter_var($formValues['amount'], FILTER_VALIDATE_FLOAT);
    $categoryId = filter_var($formValues['category_id'], FILTER_VALIDATE_INT);
    $expenseId = $formValues['expense_id'] === ''
        ? null
        : filter_var($formValues['expense_id'], FILTER_VALIDATE_INT);
    $date = DateTime::createFromFormat('!Y-m-d', $formValues['expense_date']);
    $validDate = $date !== false && $date->format('Y-m-d') === $formValues['expense_date'];
    $validCategoryIds = array_map('intval', array_column($categories, 'id'));

    if ($amount === false || $amount <= 0 || $amount > 99999999.99) {
        $errors[] = 'Enter an amount greater than zero.';
    }
    if ($categoryId === false || !in_array($categoryId, $validCategoryIds, true)) {
        $errors[] = 'Choose a category from the list.';
    }
    if (!$validDate) {
        $errors[] = 'Choose a valid expense date.';
    }
    if (function_exists('mb_strlen') && mb_strlen($formValues['description']) > 255) {
        $errors[] = 'Keep the description to 255 characters or fewer.';
    } elseif (!function_exists('mb_strlen') && strlen($formValues['description']) > 255) {
        $errors[] = 'Keep the description to 255 characters or fewer.';
    }
    if ($formValues['expense_id'] !== '' && $expenseId === false) {
        $errors[] = 'The expense record could not be identified.';
    }

    if ($errors === []) {
        $values = [
            'amount' => $amount,
            'category_id' => $categoryId,
            'expense_date' => $formValues['expense_date'],
            'description' => $formValues['description'],
            'user_id' => $userId,
        ];

        if ($expenseId === null) {
            $statement = $pdo->prepare(
                'INSERT INTO expenses (user_id, category_id, expense_amount, expense_date, description)
                 VALUES (:user_id, :category_id, :amount, :expense_date, :description)'
            );
            $statement->execute($values);
        } else {
            $values['expense_id'] = $expenseId;
            $statement = $pdo->prepare(
                'UPDATE expenses
                 SET category_id = :category_id, expense_amount = :amount,
                     expense_date = :expense_date, description = :description
                 WHERE expense_id = :expense_id AND user_id = :user_id'
            );
            $statement->execute($values);
        }

        header('Location: expenses.php?saved=1');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && isset($_GET['edit'])) {
    $editId = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);
    if ($editId) {
        $editStatement = $pdo->prepare(
            'SELECT expense_id AS id, category_id, expense_amount AS amount, expense_date, description
             FROM expenses WHERE expense_id = :expense_id AND user_id = :user_id'
        );
        $editStatement->execute(['expense_id' => $editId, 'user_id' => $userId]);
        $editingExpense = $editStatement->fetch() ?: null;
        if ($editingExpense !== null) {
            $formValues = [
                'expense_id' => (string) $editingExpense['id'],
                'amount' => (string) $editingExpense['amount'],
                'category_id' => (string) $editingExpense['category_id'],
                'expense_date' => $editingExpense['expense_date'],
                'description' => $editingExpense['description'] ?? '',
            ];
        }
    }
}

$expenseStatement = $pdo->prepare(
    'SELECT expenses.expense_id AS id, expenses.expense_amount AS amount,
            expenses.expense_date, expenses.description,
            categories.category_name AS category_name
     FROM expenses
     INNER JOIN categories ON categories.category_id = expenses.category_id
     WHERE expenses.user_id = :user_id
     ORDER BY expenses.expense_date DESC, expenses.expense_id DESC'
);
$expenseStatement->execute(['user_id' => $userId]);
$expenses = $expenseStatement->fetchAll();

$pageTitle = 'Expense history';
$activePage = 'expenses';
require __DIR__ . '/includes/header.php';
?>
<section class="welcome-row">
    <div>
        <h2>Every expense, all in one place.</h2>
        <p>Add a new expense or update a record below.</p>
    </div>
</section>

<?php if (isset($_GET['saved'])): ?>
    <div class="notice notice-success" role="status">Your expense was saved.</div>
<?php endif; ?>
<?php if (isset($_GET['deleted'])): ?>
    <div class="notice notice-success" role="status">The expense was deleted.</div>
<?php endif; ?>
<?php if ($errors !== []): ?>
    <div class="notice notice-error" role="alert">
        <strong>Please check the following:</strong>
        <ul><?php foreach ($errors as $error): ?><li><?= escape($error) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<section class="panel form-panel" id="expense-form">
    <div class="panel-heading">
        <div>
            <h2><?= $formValues['expense_id'] !== '' ? 'Edit expense' : 'Add an expense' ?></h2>
            <p>Enter the basic details about your spending.</p>
        </div>
    </div>
    <form method="post" action="expenses.php#expense-form" class="expense-form">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="expense_id" value="<?= escape($formValues['expense_id']) ?>">
        <label>
            Amount (Rs.)
            <input type="number" name="amount" min="0.01" step="0.01" max="99999999.99" required
                   placeholder="e.g. 250.00" value="<?= escape($formValues['amount']) ?>">
        </label>
        <label>
            Category
            <select name="category_id" required>
                <option value="">Choose a category</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= escape($category['id']) ?>" <?= (string) $category['id'] === $formValues['category_id'] ? 'selected' : '' ?>>
                        <?= escape($category['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>
            Date
            <input type="date" name="expense_date" required value="<?= escape($formValues['expense_date']) ?>">
        </label>
        <label class="description-field">
            Description <span class="optional-label">(optional)</span>
            <input type="text" name="description" maxlength="255" placeholder="What was this expense for?"
                   value="<?= escape($formValues['description']) ?>">
        </label>
        <div class="form-actions">
            <button class="button button-primary" type="submit">
                <?= $formValues['expense_id'] !== '' ? 'Save changes' : 'Add expense' ?>
            </button>
            <?php if ($formValues['expense_id'] !== ''): ?>
                <a class="button button-light" href="expenses.php">Cancel</a>
            <?php endif; ?>
        </div>
    </form>
</section>

<section class="panel table-panel">
    <div class="panel-heading">
        <div>
            <h2>All expenses</h2>
            <p><?= count($expenses) ?> record<?= count($expenses) === 1 ? '' : 's' ?> saved</p>
        </div>
    </div>
    <?php if ($expenses === []): ?>
        <div class="empty-state">
            <strong>No expenses recorded yet.</strong>
            <p>Your saved expenses will appear in this table.</p>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                <tr><th>Date</th><th>Category</th><th>Description</th><th>Amount</th><th>Actions</th></tr>
                </thead>
                <tbody>
                <?php foreach ($expenses as $expense): ?>
                    <tr>
                        <td><?= escape(date('M j, Y', strtotime($expense['expense_date']))) ?></td>
                        <td><span class="category-pill"><?= escape($expense['category_name']) ?></span></td>
                        <td><?= escape($expense['description'] ?: '—') ?></td>
                        <td class="amount-cell"><?= escape(format_money($expense['amount'])) ?></td>
                        <td>
                            <div class="row-actions">
                                <a class="action-link" href="expenses.php?edit=<?= escape($expense['id']) ?>#expense-form">Edit</a>
                                <form method="post" action="delete_expense.php" onsubmit="return confirm('Delete this expense? This cannot be undone.');">
                                    <input type="hidden" name="expense_id" value="<?= escape($expense['id']) ?>">
                                    <button class="action-link delete-link" type="submit">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
