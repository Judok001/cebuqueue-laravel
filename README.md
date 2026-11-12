# CebuQueue

CebuQueue is a queue management system for barangay health centers and clinics in Cebu. It supports patient reservations, priority-aware queues, staff workflows, and admin service and report management.

**Tech Stack:** PHP 8.3+ + Laravel 13 + Blade Templates + Vite + Tailwind CSS + SQLite (default database configuration).

## Setup (3 steps)

**Requirements:** PHP 8.3 or newer, Composer, Node.js, and npm.

**1. Database**

The project is configured to use SQLite by default.

```bash
copy .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed --class=ServiceSeeder
```

If the SQLite database file does not exist, create an empty file at `database/database.sqlite` before running the migrations.
The seeder adds the clinic services used by patient registration and public reservations.

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

- **Patient registration:** Staff and admins record a patient's name, address, phone, age, service, and priority; the visit ticket is created at the same time.
- **Public reservations:** Patients can reserve a service online. Public reservations always receive regular priority.
- **Queue management:** Staff and admins can call the next patient, mark the current patient served or absent, and see priority-ordered queues.
- **Queue tickets:** Tickets show queue position and an estimated wait that respects the current patient and priority order.
- **Priorities:** Emergency patients are called first; senior, PWD, and pregnant patients share the next FIFO tier; regular tickets follow in FIFO order.
- **Patient records:** Staff and admins can search patients and add existing patients to another service queue.
- **Administration:** Admins manage staff and services and view daily reports, including priority totals. Staff do not have access to these admin tools.
- **Public Now Serving Board:** Displays ticket numbers and waiting totals without patient names.
- **Authentication and profiles:** Protected staff/admin access, profile management, logout, email verification, and password reset.
- **Database migrations:** Patient, service, and queue data are stored using Laravel migrations.

## Today's Accomplishments

- Added and activated the five selected clinic services: Childhood Immunizations, Family Planning Services, Basic Laboratory Diagnostics, Infectious Disease Management, and Free Senior Maintenance Medicines. Other default services are inactive and can be re-enabled by an admin.
- Refreshed the staff dashboard with a consistent dark clinic interface, clearer queue metrics, quick actions, and priority-aware service cards.
- Updated the Profile page to use the CebuQueue staff layout while retaining profile editing, password changes, email verification, and account deletion.
- Updated the Now Serving Board to match the staff interface for signed-in users while keeping a public view for visitors. The board displays ticket numbers without patient names.
- Verified the application with 55 passing tests.

## Current Database

The included migrations create the following core tables:

- `users` — stores user account information.
- `patients` — stores patient name and contact details.
- `services` — stores clinic services and estimated service times.
- `queue_tickets` — stores ticket, visit, priority, and service status information.
- `cache` — supports database-backed caching.
- `jobs` — supports queued background jobs.

The default database connection is SQLite. MySQL can be configured separately through the `.env` file if required.

## Application Routes

- `/` and `/reserve` — public service reservation.
- `/ticket/{token}` — public ticket status, position, and estimated wait.
- `/now-serving` — public Now Serving Board.
- `/login` — staff/admin login.
- `/dashboard` — protected queue dashboard.
- `/queue` and `/patients` — staff queue and patient management.
- `/reports` and `/services-staff` — admin-only reports, services, and staff management.
- `/profile` — authenticated profile management.

SMS notifications and SMS-gateway integration are intentionally deferred to a later phase.
