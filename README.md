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

