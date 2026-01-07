# AI Agent Configuration & Instructions

## Project Overview
Cinema-Book is a modern cinema management and ticket booking system.
- **Backend**: Laravel 11.x (PHP 8.2+)
- **Frontend**: React 19.x (Vite)
- **Database**: MySQL 8.0+ (Fully normalized to 3NF)

## Technical Architecture
- **API First**: The backend acts as a RESTful API service.
- **Authentication**: Laravel Sanctum for API token-based authentication.
- **Payment Integration**: Placeholders for VNPay/ZaloPay integration.
- **Nomenclature**:
    - `rooms` (formerly `screens`)
    - `booking_details` (formerly `tickets` and `booking_seats`)
    - `cities` (normalized from `theaters.city`)

## Database Standards (3NF)
- **Avoid Redundancy**: Do not store derived data (like `movie.rating`) in tables. Use the `movie_ratings` view or model accessors.
- **Foreign Keys**: Always use `CASCADE DELETE` for child records in the booking flow (`booking_details`, `booking_combos`, `transactions`).
- **Standard Flow**: `Seat Selection` -> `Seat Lock` -> `Booking (Pending)` -> `Transaction` -> `Booking (Confirmed)`.

## Common Commands
- **Backend**: `php artisan serve`, `php artisan migrate`, `php artisan test`
- **Frontend**: `npm run dev` (or `npm start`)
- **Database**: `php artisan db:show`, `php artisan db:table [name]`

## Development Guidelines
1. **Migrations**: Always provide a `down()` method for rollback.
2. **Models**: Use Eloquent relationships and avoid raw SQL where possible.
3. **Frontend**: Keep components modular and use standard CSS.
4. **Agent Rules**: When modifying the database, ensure 3NF compliance and update documenting `.md` files in the root directory.
5. **Communication & Language**:
    - **Chat**: Use **Vietnamese** for all communication with the user.
    - **Code**: Use **English** for variable names, functions, classes, etc.
    - **Comments & Documentation**: Use **Vietnamese** for code comments and documentation files (like this one).

---

## Agent Workflows

### 1. Setup Project
Use this workflow to initialize the project environment.
- Step 1: Backend Setup
    - Copy `.env.example` to `.env`
    - Run `composer install`
    - Run `php artisan key:generate`
    - Run `php artisan migrate`
    - Start server: `php artisan serve`
- Step 2: Frontend Setup
    - Run `npm install`
    - Start development server: `npm start`

### 2. Database & 3NF Check
Use this workflow to refresh the database and verify structural integrity.
- Step 1: Refresh all migrations: `php artisan migrate:fresh --seed`
- Step 2: Verify structure: 
    - Check tables: `php artisan db:show`
    - Verify specific tables: `php artisan db:table cities`
