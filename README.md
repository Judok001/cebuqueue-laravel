# CebuQueue

CebuQueue is a web-based queue management system concept for barangay health centers and clinics in Cebu. The current project provides a foundation for user registration, login, dashboard access, and profile management using Laravel.

**Tech Stack:** PHP 8.3+ + Laravel 13 + Blade Templates + Vite + Tailwind CSS + SQLite (default database configuration).

## Setup (3 steps)

**Requirements:** PHP 8.3 or newer, Composer, Node.js, and npm.

**1. Database**

The project is configured to use SQLite by default.

```bash
copy .env.example .env
php artisan key:generate
php artisan migrate
```

If the SQLite database file does not exist, create an empty file at `database/database.sqlite` before running the migrations.

**2. Backend** (runs at http://127.0.0.1:8000)

Open a terminal in the project folder:

```bash
composer install
php artisan serve
```

If you have not configured the project yet, complete the database setup first.

**3. Frontend** (Vite development server)

Open a second terminal in the same project folder:

```bash
npm install
npm run dev
```

Open **http://127.0.0.1:8000** in your browser to access the Laravel application. Keep both terminal processes running while developing.

## Current Features

- **User Registration:** Allows new users to create an account.
- **User Login:** Authenticates users using their registered credentials.
- **Dashboard:** Provides a protected dashboard after login.
- **Profile Management:** Supports updating profile information and changing passwords.
- **Account Security:** Includes logout, password confirmation, email verification routes, and password reset functionality.
- **Database Migrations:** Uses Laravel migrations to create and manage database tables.

## Current Database

The included migrations create the following core tables:

- `users` — stores user account information.
- `cache` — supports database-backed caching.
- `jobs` — supports queued background jobs.

The default database connection is SQLite. MySQL can be configured separately through the `.env` file if required.

## Application Routes

- `/` — welcome page.
- `/register` — user registration.
- `/login` — user login.
- `/dashboard` — protected dashboard.
- `/profile` — profile management.
- `/forgot-password` — password reset request.
- `/verify-email` — email verification prompt.

## Not Built Yet

The uploaded project is currently a Laravel starter application with authentication and profile-management functionality. The following CebuQueue-specific features are not implemented in the supplied code:

- Patient queue reservation and ticket generation.
- Queue position and estimated waiting time.
- Staff accounts and staff management.
- Calling the next patient and marking patients as served or absent.
- Patient registration and walk-in management.
- Admin reports and service management.
- Public Now Serving Board.
- SMS notifications and integration with an SMS gateway.
- Queue prioritization for urgent cases, senior citizens, persons with disabilities, and pregnant patients.

## Current Milestone: User Registration and Login

The current release provides the foundation for account registration and login using Laravel's built-in authentication structure.

**User Registration**
- Route: `POST /register`
- Controller: `app/Http/Controllers/Auth/RegisteredUserController.php`
- View: `resources/views/auth/register.blade.php`
- Creates user accounts through Laravel's authentication system.

**User Login**
- Route: `POST /login`
- Controller: `app/Http/Controllers/Auth/AuthenticatedSessionController.php`
- View: `resources/views/auth/login.blade.php`
- Authenticates registered users using Laravel's session-based authentication.

**How to test:** Register an account, log in with the same credentials, open the dashboard, update your profile, and log out. Try logging in with incorrect credentials to check that authentication rejects them.

**Next milestone:** Build the actual queue management modules, including patient queues, staff and admin roles, queue processing, reports, and the Now Serving Board.
