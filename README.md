# Student Auth System

This is a simple full-stack project I made for user authentication and profile management. It uses different databases for different purposes to learn how they work together. 

## Features
* User Registration (data is saved in MySQL)
* User Login with password hashing for security
* Session management using Redis (so users stay logged in)
* Profile page where users can add their Age, Date of Birth, and Contact number
* Profile details are stored in MongoDB
* Update profile functionality using AJAX (no page reload needed)

## Tech Stack Used
* **Frontend:** HTML, CSS (Bootstrap), JavaScript (jQuery AJAX)
* **Backend:** PHP
* **Databases:**
  * MySQL (for user accounts and passwords)
  * MongoDB (for user profile information)
  * Redis (for handling login sessions)

## Setup Instructions

To run this project on your computer, you need to install XAMPP, Redis, and MongoDB.

1. **Install XAMPP:** Download and install XAMPP. Start the Apache and MySQL modules.
2. **Setup MySQL:** 
   * Go to phpMyAdmin (http://localhost/phpmyadmin)
   * Create a new database called `student_auth_db`
   * Create a `users` table with columns: `id` (auto increment), `email`, and `password`.
3. **Setup Redis:**
   * Make sure you have Redis installed and running on default port `6379`.
   * Also, make sure the Redis extension is enabled in your PHP settings (`php.ini`).
4. **Setup MongoDB:**
   * Make sure you have MongoDB installed or use a MongoDB Atlas cloud URL.
   * Install the MongoDB PHP extension (`php_mongodb.dll`) and enable it in your `php.ini`.
   * Run `composer require mongodb/mongodb` in the project folder to install the required library.

## How to run locally

1. Clone or download this project folder.
2. Place the folder inside your XAMPP `htdocs` directory (e.g. `C:\xampp\htdocs\UserAuth`).
3. Open your browser and go to `http://localhost/UserAuth/register.html` to create an account.
4. After registering, you can login and update your profile!

## Project Structure

```text
UserAuth/
│
├── css/
│   └── style.css
├── js/
│   ├── login.js
│   ├── register.js
│   └── profile.js
├── php/
│   ├── login.php
│   ├── register.php
│   └── profile.php
├── vendor/               (MongoDB composer files)
├── login.html
├── register.html
├── profile.html
└── README.md
```

## Future Improvements
* Add password reset feature
* Add profile picture upload
* Make the design look better for mobile phones
* Add more security validations

## Author
saran kumar
