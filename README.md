# Smart Booking System

A simple appointment booking app I built for an interview project. It handles availability, lets clients book slots, and prevents double-bookings.

## What it does

This is basically a booking system for service businesses - think hair salons, personal trainers, consultants, etc.

Main features:
- Client booking interface
- Admin panel for setting up working hours
- Time slot generation based on availability
- Prevents double bookings (important!)
- Support for weekly or specific date schedules

The app makes sure people can only book available slots and handles all the scheduling logic.

## Setup

1. **Install dependencies**
```bash
composer install
npm install
```

2. **Environment setup**
```bash
cp .env.example .env
php artisan key:generate
```

3. **Database**
```bash
php artisan migrate
php artisan db:seed
```

The seeder adds:
- A couple sample services (Haircut, Personal Training)
- Some basic working hours for testing

4. **Run it**
```bash
php artisan serve
npm run dev
```

Then go to `http://localhost:8000`

## API Endpoints

- `GET /api/services` - Get available services
- `GET /api/availability?date=YYYY-MM-DD&service_id=ID` - Check available slots
- `POST /api/bookings` - Book an appointment
- `POST /api/working-rules` - Set working hours (admin)

## Testing

```bash
php artisan test
```

## Tech Stack

- Backend: Laravel 11
- Frontend: Vue 3
- Database: MySQL/PostgreSQL
- Build: Vite
- Styles: Tailwind CSS
