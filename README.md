Minimal Setup Instructions

Prerequisites: PHP 8.2+, Composer, Node.js 18+, MySQL 8.0+ / MariaDB 10.1+

Clone repository

cd to project root

Backend:
composer install
cp .env.example .env
Edit .env: set DB credentials, APP_URL=http://localhost:8000
php artisan key:generate
php artisan jwt:secret
Create database: mysql -u root -p -e "CREATE DATABASE crypto_trading;"
php artisan migrate
php artisan serve --port=8000

Frontend:
npm install
npm run dev

Access application at: http://localhost:8000

API endpoints available at: http://localhost:8000/api

Vite proxy configured to forward /api requests to Laravel backend.
