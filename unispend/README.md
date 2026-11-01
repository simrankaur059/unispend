# UniSpend – Student Expense Tracker

UniSpend is a small, beginner-friendly college mini-project. Students can record expenses, set a monthly budget, view recent spending, and see a pie chart of spending by category.

## Project structure

```text
unispend/
├── assets/
│   ├── css/
│   │   └── style.css
│   └── js/
│       └── app.js
├── includes/
│   ├── db.php
│   ├── footer.php
│   ├── functions.php
│   └── header.php
├── budget.php
├── database.sql
├── delete_expense.php
├── expenses.php
├── index.php
└── README.md
```

## Run it with XAMPP

1. Install and open XAMPP. Start **Apache** and **MySQL**.
2. Copy the `unispend` folder into XAMPP's web directory, usually `C:\xampp\htdocs\unispend`.
   - In VS Code, choose **File → Open Folder** and open that `unispend` folder.
3. UniSpend connects to the existing `unisp_end_db` database. If you already have this database and its tables, do not import `database.sql`; the app uses the existing records.
4. For a new installation only, import `database.sql` in phpMyAdmin to create the database and its tables.
5. If your MySQL username or password is different from the XAMPP defaults, update the settings at the top of `includes/db.php`. The defaults are username `root` and a blank password.
6. Open `http://localhost/unispend` in a browser.
7. Add expenses from **Expense history** and set a budget from **Monthly budget**. The dashboard updates from the saved MySQL records.

## How it works

- `includes/db.php` opens the PDO connection to the `unisp_end_db` MySQL database.
- `database.sql` defines the existing database's `users`, `expenses`, `categories`, and `budgets` tables and inserts a demo student and the seven categories for a new installation.
- `expenses.php` validates and saves new expenses, lists saved records, and lets the demo student edit them.
- `delete_expense.php` removes an expense when the Delete button is used.
- `budget.php` saves a separate budget for the selected month.
- `index.php` totals the selected calendar month's budget and expenses, shows recent records, and passes category totals to the chart.
- `assets/js/app.js` draws the pie chart with the browser's canvas API; no chart package is needed.
- `assets/css/style.css` styles the sidebar, cards, forms, table, and mobile layout.

This classroom demo uses one seeded **Demo Student** (user ID `1`) and does not include sign-in. Expenses and budgets are persisted in MySQL; they are not sample rows.
