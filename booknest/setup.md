# BookNest Setup Guide

Welcome to BookNest! Follow these steps to set up the application in Laragon.

## Requirements
- **Laragon**: Ensures Apache, PHP 8, and MySQL are available.

## Setup Steps

### 1. File Placement
Make sure the `booknest` folder is inside your Laragon `www` directory (or map it in Laragon if it's placed elsewhere like `e:\booknest`). Access URL via `http://localhost/booknest` (or `booknest.test` if Laragon auto-virtual hosts).

### 2. Database Import
1. Open Laragon and click **Start All**.
2. Click **Database** (opens phpMyAdmin or HeidiSQL).
3. Create a new database named `booknest` (if not created by the import script).
4. Import the `database/booknest.sql` file.

### 3. Default Configuration
The database connection string assumes Laragon's default:
- **Host**: localhost
- **User**: root
- **Password**: `(empty)`
- **Database**: booknest

If your MySQL password is not empty, please update `config/database.php`.

### 4. Admin Access
Register a new user through `http://localhost/booknest/auth/signup.php` and login. The first user or a user specific ID can be made Admin manually by editing the database or by default implementation.
