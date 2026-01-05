# Secure Password Manager (Passman) - Security Audit & Remediation

This repository contains the source code for **Passman**, a simple PHP/MySQL password manager application. The project serves as a case study in **Web Application Security**, demonstrating the identification, exploitation, and remediation of critical security vulnerabilities.

Originally developed as an intentionally vulnerable application for an academic assignment (Aristotle University of Thessaloniki, Information Systems Security 2025-2026), this repository represents the **hardened, secure version** of the software.

## 📌 Project Overview

The goal of this project was to take a functioning but insecure web application and perform a full security audit. The process involved two phases:
1.  **Penetration Testing (Red Team):** Identifying flaws and demonstrating exploits (SQL Injection, XSS, etc.).
2.  **Remediation (Blue Team):** Refactoring the codebase to implement secure coding practices and industry standards.

## 🛡️ Vulnerabilities & Solutions

The following security flaws were identified in the original codebase and have been patched in this release:

### 1. SQL Injection (SQLi) - Authentication Bypass
* **The Problem:** The application originally concatenated user input directly into SQL queries (e.g., `SELECT * FROM users WHERE username = '$user'`). This allowed attackers to bypass login screens using payloads like `' OR 1=1 #`.
* **The Solution:** All database interactions were rewritten using **Prepared Statements** with parameterized queries. This ensures the database treats user input strictly as data, not executable code.

### 2. Stored Cross-Site Scripting (XSS)
* **The Problem:** The "Notes" feature allowed users to save raw HTML/JavaScript to the database. When other users viewed these notes, the malicious scripts would execute in their browsers (e.g., `<script>alert('Hacked')</script>`), leading to potential session hijacking.
* **The Solution:** Implemented strict **Output Encoding** using `htmlspecialchars()` before rendering any user-generated content. This converts special characters into safe HTML entities.

### 3. Insecure Password Storage
* **The Problem:** User passwords were stored in the database as **Plaintext**. A database leak would instantly compromise all user accounts.
* **The Solution:** Implemented strong **Password Hashing** using the `password_hash()` function (Bcrypt algorithm). The application now stores only the hash and verifies logins using `password_verify()`.

### 4. Excessive Database Privileges
* **The Problem:** The application connected to the database using the administrative `root` account with no password.
* **The Solution:** Enforced the **Principle of Least Privilege**. A dedicated database user (`app_user`) was created with permissions restricted strictly to the application's specific database and tables.

## 📂 Repository Structure

* `passman_new/` - The main application files.
    * `login.php` - Secure login with hash verification.
    * `register.php` - Registration handling with password hashing.
    * `dashboard.php` - Secure password management interface.
    * `notes.php` - Sanitized notes/announcements board.
    * `db_connect.php` - Centralized, secure database connection configuration.
    * `create-pwd_mgr-db-withData.sql` - SQL script to initialize the database schema.
    * `xss/` - (Legacy/Educational) A folder containing the original "hacker tools" used to demonstrate the XSS exploits during the audit phase.
* `Αρχεία Εργασίας/` - The initial application files.
* `Εργασία-Ασφάλειας 2025-2026` - The assignment in Greek.
* `KoudounisVasileios10739` - The report in Greek.

## 🚀 Installation & Setup

To run this project locally, you need a standard LAMP/WAMP stack. The instructions below assume the use of **XAMPP**.

### Prerequisites
* [XAMPP](https://www.apachefriends.org/) (Apache + MySQL/MariaDB + PHP)
* A web browser

### Step 1: Clone the Repo
Clone this repository into your web server's public directory (e.g., `C:\xampp\htdocs\`).
```bash
cd C:\xampp\htdocs
git clone [https://github.com/yourusername/passman-security-audit.git](https://github.com/yourusername/passman-security-audit.git) passman
```

### Step 2: Database Setup
1.  Start the **Apache** and **MySQL** modules in XAMPP control panel.
2.  Open your database management tool (e.g., **HeidiSQL** or **phpMyAdmin** at `http://localhost/phpmyadmin`).
3.  Create a new database named `pwd_mgr`.
4.  Import the schema file `create-pwd_mgr-db-withData.sql` (found in the repository) into this new database.

### Step 3: Create the Secure Database User
For security reasons, this application has been patched to **not** use the `root` account. You must create the limited user defined in the configuration.

Run the following SQL query in your database manager (SQL tab):

```sql
CREATE USER 'app_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT SELECT, INSERT, UPDATE, DELETE ON pwd_mgr.* TO 'app_user'@'localhost';
FLUSH PRIVILEGES;
```

Note: If you wish to use different credentials, you must update the db_connect.php file to match.

### Step 4: Run the Application
Open your web browser and navigate to: http://localhost/passman/index.html

## 🧪 How to Test the Fixes
You can verify that the security patches are working by attempting the original exploits:

1. Test SQL Injection (Fixed): * Go to the login page.

- Attempt to log in with Username: ' OR 1=1 #.

- Result: You should receive an "Invalid username or password" error. You will not be logged in as Admin.

2. Test XSS (Fixed): * Log in with a valid user.

- Post a note containing a script: <script>alert('Test')</script>.

- Result: The text should appear literally on the screen as plain text. No popup alert should appear.

3. Test Password Hashing (Fixed): * Register a new user via the Registration form.

- Inspect the login_users table in your database tool.

- Result: The password column will contain a long hash string (starting with $2y$...) instead of the plaintext password.

## ⚠️ Disclaimer
This project is for educational purposes only. The code provided in the xss/ folder includes scripts designed to demonstrate vulnerabilities and should only be used in a controlled, local environment. Do not use the vulnerability demonstration tools on servers you do not own or have explicit permission to test.
