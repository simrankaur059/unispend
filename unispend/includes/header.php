<?php
declare(strict_types=1);

$pageTitle = $pageTitle ?? 'UniSpend';
$activePage = $activePage ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= escape($pageTitle) ?> | UniSpend</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="app-shell">
    <aside class="sidebar">
        <a class="brand" href="index.php">
            <span class="brand-mark">U</span>
            <span>UniSpend<small>Student Expense Tracker</small></span>
        </a>
        <nav class="navigation" aria-label="Main navigation">
            <a class="<?= $activePage === 'dashboard' ? 'active' : '' ?>" href="index.php">
                <span aria-hidden="true">⌂</span> Dashboard
            </a>
            <a class="<?= $activePage === 'expenses' ? 'active' : '' ?>" href="expenses.php">
                <span aria-hidden="true">↗</span> Expense history
            </a>
            <a class="<?= $activePage === 'budget' ? 'active' : '' ?>" href="budget.php">
                <span aria-hidden="true">◎</span> Monthly budget
            </a>
        </nav>
        <div class="sidebar-note">
            <strong>Small steps add up.</strong>
            <p>Keep track of today, plan for tomorrow.</p>
        </div>
    </aside>
    <main class="main-content">
        <header class="topbar">
            <div>
                <p class="eyebrow">YOUR MONEY, MADE SIMPLE</p>
                <h1><?= escape($pageTitle) ?></h1>
            </div>
            <div class="profile-chip"><span class="profile-avatar">S</span> Demo Student</div>
        </header>
