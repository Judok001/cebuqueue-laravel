# Copilot instructions

## Commands

- Install PHP dependencies with `composer install`.
- Run the full PHP test suite with `composer test` (or `php artisan test`).
- Run a test file with `php artisan test tests/Feature/Auth/AuthenticationTest.php`.
- Run one test method with `php artisan test --filter=test_users_can_authenticate_using_the_login_screen`.
- The test configuration uses in-memory SQLite, so PHP's `pdo_sqlite` extension must be enabled.
- Check PHP formatting with `php vendor/bin/pint --test`; apply formatting with `php vendor/bin/pint`.
- Build frontend assets with `npm run build`; use `npm run dev` for Vite's development server.

## Architecture

- This is a Laravel 13 server-rendered application. `routes/web.php` defines web pages and includes `routes/auth.php`, which holds login, password recovery, email verification, and logout routes; public registration is disabled. Controllers, form requests, and Eloquent models live under `app/`.
- Authentication and profile management are the established backend flows: auth controllers are in `app/Http/Controllers/Auth/`, profile behavior is in `ProfileController` and `ProfileUpdateRequest`, and `User` is the current application model. `User::ROLE_ADMIN` and `User::ROLE_STAFF` determine dashboard navigation and route access; only admins can create staff accounts through `StaffController`. Public registration is disabled. Schema changes belong in `database/migrations/`.
- The interface is Blade-based under `resources/views/`. Auth screens use the CebuQueue guest layout; the dashboard uses the CebuQueue application layout. Frontend entry points are `resources/css/app.css` and `resources/js/app.js`, bundled by Vite; Alpine is initialized in the JavaScript entry point.
- `tests/Feature/` exercises HTTP and authentication behavior; `tests/Unit/` contains unit tests. PHPUnit configures tests to use an in-memory SQLite database.
- Queue, patient, appointment, and report routes are currently scaffolding rather than complete domain flows. Check the existing route and view implementation before treating these as persistent features.

## Conventions

- Keep web endpoints in the appropriate route file and use their named routes in Blade with `route(...)`; the CebuQueue shell uses `request()->routeIs(...)` to mark the active navigation item.
- Follow the existing Laravel request/controller pattern for validated writes: put input rules in a Form Request and use its validated data in the controller.
- Follow the current `User` model style: model fillable/hidden attributes use PHP attributes, while attribute casts are returned by the `casts()` method.
- Add persistent feature data through migrations and Eloquent models; keep database-dependent feature tests isolated with Laravel's `RefreshDatabase` trait, as the existing feature tests do.
