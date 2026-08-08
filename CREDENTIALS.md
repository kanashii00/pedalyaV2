# Pedalya — Login Credentials

Local development web app. Base URL: `http://localhost:8000`

## Admin Panel

| Role  | Email              | Password | URL            |
|-------|--------------------|----------|----------------|
| Admin | admin@pedalya.com  | `password` | /admin         |

## Rider App

All riders use the same password.

| Name          | Email                         | Password | URL         |
|---------------|-------------------------------|----------|-------------|
| Juan Dela Cruz| juan.delacruz@jmc.edu.ph      | `password` | /rider     |
| Maria Santos  | maria.santos@jmc.edu.ph       | `password` | /rider     |
| Jose Reyes    | jose.reyes@jmc.edu.ph         | `password` | /rider     |
| Ana Garcia    | ana.garcia@jmc.edu.ph         | `password` | /rider     |
| Pedro Mendoza | pedro.mendoza@jmc.edu.ph      | `password` | /rider     |

> Seeded via `database/seeders/AdminSeeder.php` and `database/seeders/RiderSeeder.php`
> (password hashed with `Hash::make('password')`).

## Quick Login Links

- Admin dashboard: https://localhost:8000/login
- Register a new rider: https://localhost:8000/register