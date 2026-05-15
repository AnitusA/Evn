# User Portal

Registration -> Login -> Profile workflow built with separate HTML, CSS, JS, and PHP files.

## Stack

- HTML
- CSS
- JavaScript with jQuery AJAX
- PHP
- MySQL for registration/auth data
- MongoDB for profile details
- Redis for session storage

## Run locally (Docker)

1. Start all services:

   ```bash
   docker compose up --build -d
   ```

2. Open the app:

   - http://localhost:8080

3. Stop services:

   ```bash
   docker compose down
   ```

## Required setup (without Docker)

1. Create a MySQL database named `sem4_lab`.
2. Create a `users` table with columns:
   - `id` INT AUTO_INCREMENT PRIMARY KEY
   - `full_name` VARCHAR(150) NOT NULL
   - `username` VARCHAR(100) NOT NULL UNIQUE
   - `email` VARCHAR(150) NOT NULL UNIQUE
   - `password_hash` VARCHAR(255) NOT NULL
   - `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
3. Create the MongoDB collection `sem4_lab.profiles`.
4. Start Redis on `127.0.0.1:6379`.
5. Update database credentials in `php/config.php` if needed.

## Notes

- Login state is stored in browser `localStorage`.
- The backend session token is stored in Redis.
- Profile details are stored in MongoDB.