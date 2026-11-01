<?php
declare(strict_types=1);

require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/functions.php';

$userId = 1;
$currentMonth = date('Y-m');
$selectedMonth = $currentMonth;
$errors = [];
$saved = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedMonth = trim((string) ($_POST['budget_month'] ?? ''));
    $amountInput = trim((string) ($_POST['amount'] ?? ''));
    $amount = filter_var($amountInput, FILTER_VALIDATE_FLOAT);

    if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $selectedMonth)) {
        $errors[] = 'Choose a valid month.';
    }
    if ($amount === false || $amount < 0 || $amount > 99999999.99) {
        $errors[] = 'Enter a budget of zero or more.';
    }

    if ($errors === []) {
        $statement = $pdo->prepare(
            'INSERT INTO budgets (user_id, budget_month, budget_amount)
             VALUES (:user_id, :budget_month, :amount)
             ON DUPLICATE KEY UPDATE budget_amount = VALUES(budget_amount)'
        );
        $statement->execute([
            'user_id' => $userId,
            'budget_month' => $selectedMonth,
            'amount' => $amount,
        ]);
        $saved = true;
    }
}

$selectedMonth = preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $selectedMonth)
    ? $selectedMonth
    : $currentMonth;
$monthStart = $selectedMonth . '-01';
$nextMonthStart = date('Y-m-01', strtotime($monthStart . ' +1 month'));

$budgetStatement = $pdo->prepare(
    'SELECT budget_amount FROM budgets WHERE user_id = :user_id AND budget_month = :budget_month'
);
$budgetStatement->execute(['user_id' => $userId, 'budget_month' => $selectedMonth]);
$monthlyBudget = (float) ($budgetStatement->fetchColumn() ?: 0);

$spentStatement = $pdo->prepare(
    'SELECT COALESCE(SUM(expense_amount), 0) FROM expenses
     WHERE user_id = :user_id AND expense_date >= :month_start AND expense_date < :next_month_start'
);
$spentStatement->execute([
    'user_id' => $userId,
    'month_start' => $monthStart,
    'next_month_start' => $nextMonthStart,
]);
$monthlyExpenses = (float) $spentStatement->fetchColumn();
$remaining = $monthlyBudget - $monthlyExpenses;
$progress = $monthlyBudget > 0 ? min(100, ($monthlyExpenses / $monthlyBudget) * 100) : 0;

$pageTitle = 'Monthly budget';
$activePage = 'budget';
require __DIR__ . '/includes/header.php';
?>
<section class="welcome-row">
    <div>
        <h2>A little planning goes a long way.</h2>
        <p>Set a spending limit and check how much of it you have used.</p>
    </div>
</section>

<?php if ($saved): ?>
    <div class="notice notice-success" role="status">Your budget for <?= escape(date('F Y', strtotime($monthStart))) ?> was saved.</div>
<?php endif; ?>
<?php if ($errors !== []): ?>
    <div class="notice notice-error" role="alert">
        <strong>Please check the following:</strong>
        <ul><?php foreach ($errors as $error): ?><li><?= escape($error) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<section class="budget-grid">
    <article class="panel budget-form-panel">
        <div class="panel-heading">
            <div>
                <h2>Set your monthly budget</h2>
                <p>You can save a different budget for each month.</p>
            </div>
        </div>
        <form method="post" action="budget.php" class="budget-form">
            <label>
                Month
                <input type="month" name="budget_month" required value="<?= escape($selectedMonth) ?>">
            </label>
            <label>
                Budget amount (Rs.)
                <input type="number" name="amount" min="0" max="99999999.99" step="0.01" required
                       placeholder="e.g. 10000.00" value="<?= escape($monthlyBudget) ?>">
            </label>
            <button class="button button-primary" type="submit">Save budget</button>
        </form>
    </article>

    <article class="panel budget-summary">
        <div class="panel-heading">
            <div>
                <h2><?= escape(date('F Y', strtotime($monthStart))) ?> overview</h2>
                <p>Your budget compared with expenses</p>
            </div>
        </div>
        <div class="budget-total-row">
            <span>Spent so far</span>
            <strong><?= escape(format_money($monthlyExpenses)) ?></strong>
        </div>
        <div class="progress-track" role="progressbar" aria-label="Monthly budget used"
             aria-valuenow="<?= escape((int) round($progress)) ?>" aria-valuemin="0" aria-valuemax="100">
            <span style="width: <?= escape($progress) ?>%"></span>
        </div>
        <div class="budget-total-row budget-caption">
            <span><?= escape(number_format($progress, 0)) ?>% of <?= escape(format_money($monthlyBudget)) ?></span>
            <span class="<?= $remaining < 0 ? 'text-danger' : '' ?>">
                <?= $remaining < 0 ? 'Over by ' : 'Left: ' ?><?= escape(format_money(abs($remaining))) ?>
            </span>
        </div>
        <?php if ($monthlyBudget <= 0): ?>
            <p class="helper-text">Set a budget above to see your spending progress.</p>
        <?php elseif ($remaining < 0): ?>
            <p class="notice-inline notice-inline-warning">You have spent more than this month's budget.</p>
        <?php else: ?>
            <p class="notice-inline">Nice work. Keep an eye on your spending as the month goes on.</p>
        <?php endif; ?>
    </article>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
