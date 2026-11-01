<?php
declare(strict_types=1);

require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/functions.php';

$userId = 1;
$monthStart = date('Y-m-01');
$nextMonthStart = date('Y-m-01', strtotime($monthStart . ' +1 month'));
$budgetMonth = date('Y-m');
$monthLabel = date('F Y');

$budgetStatement = $pdo->prepare(
    'SELECT budget_amount FROM budgets WHERE user_id = :user_id AND budget_month = :budget_month'
);
$budgetStatement->execute(['user_id' => $userId, 'budget_month' => $budgetMonth]);
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
$remainingBalance = $monthlyBudget - $monthlyExpenses;

$categoryStatement = $pdo->prepare(
    'SELECT categories.category_name AS name, SUM(expenses.expense_amount) AS total
     FROM expenses
     INNER JOIN categories ON categories.category_id = expenses.category_id
     WHERE expenses.user_id = :user_id
       AND expenses.expense_date >= :month_start
       AND expenses.expense_date < :next_month_start
     GROUP BY categories.category_id, categories.category_name
     ORDER BY total DESC'
);
$categoryStatement->execute([
    'user_id' => $userId,
    'month_start' => $monthStart,
    'next_month_start' => $nextMonthStart,
]);
$categoryTotals = $categoryStatement->fetchAll();

$recentStatement = $pdo->prepare(
    'SELECT expenses.expense_id AS id, expenses.expense_amount AS amount,
            expenses.expense_date, expenses.description,
            categories.category_name AS category_name
     FROM expenses
     INNER JOIN categories ON categories.category_id = expenses.category_id
     WHERE expenses.user_id = :user_id
     ORDER BY expenses.expense_date DESC, expenses.expense_id DESC
     LIMIT 5'
);
$recentStatement->execute(['user_id' => $userId]);
$recentExpenses = $recentStatement->fetchAll();

$pageTitle = 'Dashboard';
$activePage = 'dashboard';
require __DIR__ . '/includes/header.php';
?>
<section class="welcome-row">
    <div>
        <h2>Good spending starts with a clear picture.</h2>
        <p>Here is your overview for <?= escape($monthLabel) ?>.</p>
    </div>
    <a class="button button-primary" href="expenses.php#expense-form">+ Add an expense</a>
</section>

<section class="summary-grid" aria-label="Monthly summary">
    <article class="summary-card">
        <div class="summary-icon icon-purple">◎</div>
        <p class="summary-label">Monthly budget</p>
        <p class="summary-value"><?= escape(format_money($monthlyBudget)) ?></p>
        <a class="card-link" href="budget.php">Manage your budget <span aria-hidden="true">→</span></a>
    </article>
    <article class="summary-card">
        <div class="summary-icon icon-orange">↗</div>
        <p class="summary-label">Total expenses</p>
        <p class="summary-value"><?= escape(format_money($monthlyExpenses)) ?></p>
        <p class="summary-caption">Spent in <?= escape($monthLabel) ?></p>
    </article>
    <article class="summary-card">
        <div class="summary-icon icon-green">✓</div>
        <p class="summary-label">Remaining balance</p>
        <p class="summary-value <?= $remainingBalance < 0 ? 'text-danger' : '' ?>">
            <?= $monthlyBudget > 0 ? escape(format_money($remainingBalance)) : '—' ?>
        </p>
        <p class="summary-caption">
            <?= $monthlyBudget > 0 ? 'Budget left for this month' : 'Set a budget to track your balance' ?>
        </p>
    </article>
</section>

<section class="content-grid">
    <article class="panel chart-panel">
        <div class="panel-heading">
            <div>
                <h2>Spending by category</h2>
                <p>Your expenses for <?= escape($monthLabel) ?></p>
            </div>
            <span class="subtle-badge">This month</span>
        </div>
        <?php if ($categoryTotals === []): ?>
            <div class="empty-chart">
                <span class="empty-icon">◔</span>
                <strong>No spending to chart yet</strong>
                <p>Add an expense and your category breakdown will appear here.</p>
            </div>
        <?php else: ?>
            <div class="chart-layout">
                <canvas id="category-chart" width="280" height="280" aria-label="Pie chart of monthly expenses by category" role="img"></canvas>
                <ul class="chart-legend" id="chart-legend"></ul>
            </div>
            <script id="category-chart-data" type="application/json"><?= json_encode($categoryTotals, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
        <?php endif; ?>
    </article>

    <article class="panel recent-panel">
        <div class="panel-heading">
            <div>
                <h2>Recent expenses</h2>
                <p>Your latest recorded spending</p>
            </div>
            <a class="text-link" href="expenses.php">See all <span aria-hidden="true">→</span></a>
        </div>
        <?php if ($recentExpenses === []): ?>
            <div class="empty-state">
                <strong>Your list is ready when you are.</strong>
                <p>Add your first expense to get started.</p>
            </div>
        <?php else: ?>
            <div class="recent-list">
                <?php foreach ($recentExpenses as $expense): ?>
                    <div class="recent-item">
                        <div class="category-avatar"><?= escape(substr($expense['category_name'], 0, 1)) ?></div>
                        <div class="recent-details">
                            <strong><?= escape($expense['category_name']) ?></strong>
                            <span><?= escape($expense['description'] ?: date('M j', strtotime($expense['expense_date']))) ?></span>
                        </div>
                        <strong class="recent-amount">-<?= escape(format_money($expense['amount'])) ?></strong>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </article>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
