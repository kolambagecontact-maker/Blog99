# Blog99.

**IN2120 — Web Programming Take Home Assignment**
University of Moratuwa · Faculty of Information Technology

---

## About

**Blog99.** is a modern, responsive publishing and blog platform built with fundamental web technologies. It provides a clean reading experience, a distraction-free writing environment, real-time live Markdown preview, dynamic story search, user authentication, and strict ownership authorization.

## Features

- **Editorial Homepage**: Typographic cover hero section with a dynamic featured story and clean latest stories feed.
- **Dynamic Search**: Live database search across titles, story contents, and author usernames (`index.php?q=...`).
- **User Authentication**: Secure registration, login, and session management using `password_hash()` (bcrypt) and `password_verify()`.
- **Story Publishing (CRUD)**: Create, read, update, and delete stories.
- **Distraction-Free Editor**: Writing workspace with Markdown formatting shortcuts and instant live rendered preview.
- **Strict Authorization**: Server-side ownership verification preventing unauthorized updates or deletions via URL manipulation.
- **Author Attribution**: User letter avatars, publication dates, and calculated estimated reading times.
- **Interactive Management**: My Stories dashboard with confirmation deletion modals.
- **Responsive Layout**: Fluid experience optimized for desktop, tablet, and mobile devices.

## Technologies Used

| Layer    | Technology |
|:---|:---|
| **Frontend** | HTML5, CSS3 (Custom Design System), Vanilla JavaScript |
| **Backend**  | PHP |
| **Database** | MySQL (with PDO Prepared Statements) |

*No heavy frameworks, Node.js, or complex build tools are required.*

---

## Installation & Setup (Local XAMPP)

### 1. Place project files in XAMPP web root
Copy the `inkwell` folder to:
```
C:\xampp\htdocs\inkwell
```

### 2. Import MySQL Database Schema
1. Start **Apache** and **MySQL** in XAMPP Control Panel.
2. Open phpMyAdmin at `http://localhost/phpmyadmin`.
3. Create a new database named `inkwell_db` (or import directly from file).
4. Click **Import**, select `database/schema.sql`, and click **Go**.

### 3. Verify Database Credentials
Check `config/database.php` (default local credentials for XAMPP):
```php
$db_host = 'localhost';
$db_name = 'inkwell_db';
$db_user = 'root';
$db_pass = '';
```

### 4. Launch in Browser
Open:
```
http://localhost/inkwell/
```
*(Or if using the PHP built-in server: `http://localhost:8080/`)*

---

## Project Structure

```
inkwell/
├── index.php                 # Homepage (Editorial hero, featured story & search)
├── article.php               # Single article reading page
├── editor.php                # Distraction-free story editor with live preview
├── my-posts.php              # User story management dashboard
├── delete-post.php           # Secure POST-only deletion handler
├── login.php                 # User login page
├── register.php              # User registration page
├── logout.php                # Session destruction & logout handler
├── config/
│   ├── database.php          # Database PDO connection (gitignored)
│   └── database.example.php  # Example config template for repository
├── includes/
│   ├── auth.php              # Authentication & session helpers
│   ├── helpers.php           # Sanitization, excerpt, date & avatar utilities
│   ├── header.php            # Shared navbar with desktop/mobile search & profile dropdown
│   └── footer.php            # Shared footer, delete modal & script includes
├── assets/
│   ├── css/
│   │   └── style.css         # Complete editorial design system
│   └── js/
│       └── app.js            # Client-side Markdown rendering & UI interactions
├── database/
│   └── schema.sql            # MySQL table creation scripts
├── .gitignore
└── README.md
```

---

## Author

**Chamika Kolambage**  
**Student Index: 245049L**  
**GitHub: kolambagecontact-maker**  

University of Moratuwa  
Faculty of Information Technology
