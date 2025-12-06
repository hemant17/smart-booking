# Smart Booking Scheduler

A full-stack appointment booking system with availability management and conflict prevention.

## Overview

Smart Booking Scheduler is a SPA (Single Page Application) built for service-based businesses to manage appointments and working schedules. The system features:

- **Client Booking Interface**: Browse services, view available time slots, and book appointments
- **Admin Management**: Create and manage working rules with flexible scheduling options
- **Real-time Availability**: Automatically prevents double bookings and conflicts
- **Flexible Scheduling**: Support for weekly recurring and specific date-based working rules

## Tech Stack

- **Backend**: Laravel 11
- **Frontend**: Vue 3 with Composition API
- **Build Tool**: Vite
- **Styling**: Tailwind CSS
- **Database**: MySQL/PostgreSQL

## Setup Instructions

### Prerequisites
- PHP 8.2+
- Composer
- Node.js & npm
- MySQL or PostgreSQL

### 1. Install Dependencies
```bash
composer install
npm install
```

### 2. Environment Configuration
```bash
cp .env.example .env
php artisan key:generate
```

Configure your database in `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=smart_booking
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 3. Database Setup
```bash
php artisan migrate
php artisan db:seed
```

The seeder will create:
- 2 services: Haircut (30 min), Personal Training (60 min)
- 2 weekly working rules: Monday & Tuesday, 09:00-17:00, 30-min intervals

### 4. Start Development Servers
```bash
# Terminal 1: Laravel backend
php artisan serve

# Terminal 2: Vite frontend
npm run dev
```

Visit `http://localhost:8000` to access the application.

## API Routes

### Services
- `GET /api/services` - List all available services

### Availability
- `GET /api/availability?date={date}&service_id={id}` - Get available slots for a specific date and service

### Bookings
- `POST /api/bookings` - Create a new appointment
  ```json
  {
    "service_id": 1,
    "start_at": "2025-01-15T10:00:00",
    "client_email": "client@example.com"
  }
  ```

### Working Rules
- `POST /api/working-rules` - Create a new working rule
  ```json
  {
    "type": "weekly",
    "weekday": 1,
    "start_time": "09:00",
    "end_time": "17:00",
    "slot_interval": 30,
    "active": true
  }
  ```

## Features & Edge Cases Handled

### Booking System
- **Conflict Prevention**: Automatically checks for overlapping appointments
- **Past Date Protection**: Cannot book time slots that have already passed
- **Service Duration**: Slot generation considers service duration
- **Unique Constraints**: Database-level uniqueness prevents double bookings

### Working Rules
- **Weekly Recurring**: Set regular working hours for specific weekdays (1-7, where 1=Monday)
- **Specific Dates**: Create custom schedules for particular dates
- **Flexible Intervals**: Configurable time slot intervals (in minutes)
- **Active Toggle**: Enable/disable rules without deleting them

### Frontend
- **Reactive UI**: Vue 3 Composition API for responsive user experience
- **Real-time Updates**: Automatic slot availability refresh after booking
- **Form Validation**: Client-side and server-side validation
- **Error Handling**: User-friendly error messages for conflicts and validation failures

## Database Schema

### Services
- `id` - Primary key
- `name` - Service name
- `duration_minutes` - Service duration in minutes
- `timestamps` - Created/updated timestamps

### Working Rules
- `id` - Primary key
- `type` - "weekly" or "date"
- `weekday` - Day of week (1-7) for weekly rules
- `date` - Specific date for date rules
- `start_time` - Working day start time
- `end_time` - Working day end time
- `slot_interval` - Time slot interval in minutes
- `active` - Rule status

### Appointments
- `id` - Primary key
- `service_id` - Foreign key to services
- `client_email` - Client email address
- `start_at` - Appointment start time
- `end_at` - Appointment end time
- `timestamps` - Created/updated timestamps
- Unique index on `(service_id, start_at)` to prevent double bookings

## Application Routes

- `/` - Client booking page
- `/admin/rules` - Admin working rules management

## Development

### Adding New Features
1. Create database migrations if needed
2. Update models with relationships
3. Add API endpoints in `routes/api.php`
4. Create/update controllers in `app/Http/Controllers/Api/`
5. Update Vue components in `resources/js/pages/`

### Testing
```bash
php artisan test
npm run test
```

## License

This project is open-source and available under the [MIT License](LICENSE).
