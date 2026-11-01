CREATE DATABASE IF NOT EXISTS unisp_end_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE unisp_end_db;

CREATE TABLE IF NOT EXISTS users (
    user_id INT NOT NULL AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL DEFAULT '',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS categories (
    category_id INT NOT NULL AUTO_INCREMENT,
    category_name VARCHAR(50) NOT NULL UNIQUE,
    PRIMARY KEY (category_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS expenses (
    expense_id INT NOT NULL AUTO_INCREMENT,
    user_id INT NOT NULL,
    category_id INT NOT NULL,
    expense_amount DECIMAL(10,2) NOT NULL,
    expense_date DATE NOT NULL,
    description VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (expense_id),
    INDEX idx_expenses_user_date (user_id, expense_date),
    CONSTRAINT fk_expenses_user FOREIGN KEY (user_id) REFERENCES users (user_id) ON DELETE CASCADE,
    CONSTRAINT fk_expenses_category FOREIGN KEY (category_id) REFERENCES categories (category_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS budgets (
    budget_id INT NOT NULL AUTO_INCREMENT,
    user_id INT NOT NULL,
    budget_month VARCHAR(7) NOT NULL,
    budget_amount DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (budget_id),
    UNIQUE KEY unique_user_month (user_id, budget_month),
    CONSTRAINT fk_budgets_user FOREIGN KEY (user_id) REFERENCES users (user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT IGNORE INTO users (user_id, full_name, email)
VALUES (1, 'Demo Student', 'student@unisp-end.local');

INSERT IGNORE INTO categories (category_name) VALUES
    ('College fees'),
    ('Transport'),
    ('Hostel'),
    ('Food'),
    ('Stationery'),
    ('Shopping'),
    ('Other');
