# 💸 UniSpend – Student Expense Tracker

![PHP](https://img.shields.io/badge/PHP-8.0%2B-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-InnoDB-4479A1?logo=mysql&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-Vanilla-F7DF1E?logo=javascript&logoColor=black)
![XAMPP](https://img.shields.io/badge/Runs%20on-XAMPP-FB7A24?logo=xampp&logoColor=white)

UniSpend is a small, beginner-friendly web app that helps students keep track of their spending. Record expenses, set a monthly budget, and see where your money goes with a clean dashboard and a category pie chart.

Built as a college mini-project using plain **PHP**, **MySQL**, and **vanilla JavaScript**, with no frameworks and no chart libraries.

<!-- Add a screenshot of your dashboard here:
![UniSpend dashboard](docs/dashboard.png)
-->

## ✨ Features

- **Dashboard** – monthly budget, total spent, remaining balance, the 5 most recent expenses, and a pie chart of this month's spending by category.
- **Expense history** – add, edit, and delete expenses from one page, with a confirmation prompt before deleting.
- **Monthly budget** – save a separate budget for each month and track usage with a progress bar.
- **Overspend alerts** – the remaining balance turns red and a warning appears when you go over budget.
- **Server-side validation** – amount, category, date, and description length are all checked before saving.
- **Canvas pie chart** – drawn with the browser's Canvas API, so there is nothing extra to install.
- **Responsive design** – sidebar layout with cards and tables that adapt to smaller screens.

## 🛠️ Tech stack

| Layer     | Technology                                              |
|-----------|---------------------------------------------------------|
| Backend   | PHP 8.0+ (strict types, PDO)                            |
| Database  | MySQL / MariaDB (InnoDB, `utf8mb4`)                     |
| Frontend  | HTML, CSS, vanilla JavaScript (Canvas API)              |
| Fonts     | DM Sans and Manrope via Google Fonts (needs internet)   |
| Local dev | XAMPP (Apache + MySQL)                                  |

##  Project structure

```text
unispend/
├── assets/
│   ├── css/
│   │   └── style.css        # Layout, cards, forms, table, responsive styles
│   └── js/
│       └── app.js           # Draws the category pie chart and legend
├── includes/
│   ├── db.php               # PDO connection to MySQL
│   ├── functions.php        # escape() and format_money() helpers
│   ├── header.php           # Page shell, sidebar, and top bar
│   └── footer.php           # Footer and script include
├── budget.php               # Set and view the monthly budget
├── database.sql             # Database schema and seed data
├── delete_expense.php       # Deletes an expense (POST only)
├── expenses.php             # Add, edit, and list expenses
├── index.php                # Dashboard
└── README.md
```

##  Getting started

### Prerequisites

- [XAMPP](https://www.apachefriends.org/) (or any stack with Apache, PHP 8.0+, and MySQL/MariaDB)

### Installation

1. **Clone the repository** into XAMPP's web directory:

   ```bash
   cd C:\xampp\htdocs
   git clone https://github.com/<your-username>/unispend.git
   ```

   Or download the ZIP and extract it so the folder is `C:\xampp\htdocs\unispend`.

2. **Start Apache and MySQL** from the XAMPP control panel.

3. **Create the database.** Open [phpMyAdmin](http://localhost/phpmyadmin), go to the **Import** tab, choose `database.sql`, and click **Go**. This creates:
   - the `unisp_end_db` database and its four tables
   - a demo student (user ID `1`)
   - seven default categories

   > Already have a `unisp_end_db` database with these tables? Skip this step. The app will use your existing records.

4. **Check the database settings** at the top of `includes/db.php`. The defaults match a fresh XAMPP install:

   ```php
   $databaseHost = 'localhost';
   $databaseName = 'unisp_end_db';
   $databaseUser = 'root';
   $databasePassword = '';
   ```

5. **Open the app** at **http://localhost/unispend**.

##  Database

The `unisp_end_db` database has four tables:

| Table        | Purpose                                         | Main columns                                                                              |
|--------------|-------------------------------------------------|-------------------------------------------------------------------------------------------|
| `users`      | Students (one demo student is seeded)           | `user_id`, `full_name`, `email`, `password`                                               |
| `categories` | Expense categories                              | `category_id`, `category_name`                                                            |
| `expenses`   | Individual expense records                      | `expense_id`, `user_id`, `category_id`, `expense_amount`, `expense_date`, `description`   |
| `budgets`    | One budget per user per month                   | `budget_id`, `user_id`, `budget_month` (`YYYY-MM`), `budget_amount`                       |

**Relationships**

- `expenses.user_id` → `users.user_id` (deleting a user also deletes their expenses)
- `expenses.category_id` → `categories.category_id`
- `budgets.user_id` → `users.user_id` (deleting a user also deletes their budgets)
- `budgets` has a unique key on `(user_id, budget_month)`, so saving a month's budget again updates it rather than creating a duplicate.

**Default categories:** College fees, Transport, Hostel, Food, Stationery, Shopping, Other.

##  How it works

| File                    | Responsibility                                                                                                   |
|-------------------------|------------------------------------------------------------------------------------------------------------------|
| `includes/db.php`       | Opens the PDO connection with exceptions enabled and associative fetch mode.                                     |
| `includes/functions.php`| `escape()` for safe HTML output and `format_money()` for `Rs.` amounts.                                          |
| `index.php`             | Totals this month's budget and expenses, lists recent records, and passes category totals to the chart as JSON.  |
| `expenses.php`          | Validates and saves new expenses, handles edits, and lists every record.                                         |
| `delete_expense.php`    | Deletes one expense, only via `POST` and only for the owning user.                                               |
| `budget.php`            | Saves a budget with `INSERT ... ON DUPLICATE KEY UPDATE` and shows spent, remaining, and progress for any month.|
| `assets/js/app.js`      | Reads the chart JSON and draws the pie slices and legend on a `<canvas>`.                                        |

##  Validation and security

- Every query uses **PDO prepared statements** to prevent SQL injection.
- All output goes through `escape()` (`htmlspecialchars`) to prevent XSS.
- Chart data is embedded with `JSON_HEX_*` flags so it cannot break out of its `<script>` tag.
- Expense amounts must be greater than zero; budgets may be zero; both are capped at `99,999,999.99`.
- Dates must be real calendar dates (`YYYY-MM-DD`) and budget months must match `YYYY-MM`.
- The submitted category must exist in the `categories` table.
- Descriptions are limited to 255 characters.
- Edit and delete queries include `user_id`, so records are scoped to their owner.
- Deleting is `POST`-only and asks for confirmation first.

##  Limitations

This is a classroom demo, so it is intentionally simple:

- **No login or registration.** Everything is tied to the seeded **Demo Student** (user ID `1`).
- The `password` column exists but is not used yet.
- No CSRF protection on forms, which would be needed before any real deployment.
- Currency is fixed to rupees (`Rs.`).
- The dashboard and pie chart cover the **current calendar month** only.

##  Ideas for improvement

- [ ] User registration and sign-in with `password_hash()`
- [ ] CSRF tokens on forms
- [ ] Filter and search expenses by date, category, or keyword
- [ ] Export expenses to CSV
- [ ] Per-category budgets and spending alerts
- [ ] Monthly and yearly trend charts
- [ ] Choose any month on the dashboard

##  Troubleshooting

| Problem | Fix |
|---------|-----|
| "Could not connect to MySQL" | Start MySQL in XAMPP and check the credentials in `includes/db.php`. |
| "Unknown database `unisp_end_db`" | Import `database.sql` in phpMyAdmin. |
| Pie chart is empty | The chart shows the current month only. Add an expense dated this month. |
| 404 Not Found | Make sure the folder is named `unispend` and sits directly inside `htdocs`. |
| Fonts look different | Fonts load from Google Fonts, so they need an internet connection. |

##  Contributing

Suggestions and pull requests are welcome. For larger changes, please open an issue first to discuss what you would like to change.

## 📄 License

No license has been added yet. If you want others to reuse your code, add a `LICENSE` file (for example [MIT](https://choosealicense.com/licenses/mit/)) and update this section.

## 👤 Author

Made by Simranpreet Kaur as a college mini-project.
