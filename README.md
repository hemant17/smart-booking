# Smart Booking Scheduler

A full-stack appointment booking system with real-time availability, rule-based scheduling, and conflict-free bookings.  
Built as a coding assignment to demonstrate backend logic, frontend SPA architecture, and clean API design.

---

## 🚀 Overview

Smart Booking Scheduler is a **Single Page Application (SPA)** designed for service-based businesses such as hairdressers, fitness coaches, and consultants.

It provides:

- **Client-facing booking system**  
- **Admin interface for defining working hours**  
- **Dynamic time-slot generation** based on rules  
- **Automatic prevention of double bookings**  
- **Weekly recurring or date-specific working hours**  

The system ensures accurate availability and reliable booking workflows.

---

## 🛠 Tech Stack

| Layer      | Technology |
|------------|-----------|
| Backend    | Laravel 11 (REST API) |
| Frontend   | Vue 3 (Composition API) |
| Build Tool | Vite |
| Styling    | Tailwind CSS |
| Database   | MySQL / PostgreSQL |

---

## 📦 Installation

### 1. Install dependencies

```bash
composer install
npm install
```

### 2. Environment setup

```bash
cp .env.example .env
php artisan key:generate
```

Configure your database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=smart_booking
DB_USERNAME=root
DB_PASSWORD=secret
```

### 3. Run migrations & seeders

```bash
php artisan migrate
php artisan db:seed
```

Database seeding includes:

- **Two services** (Haircut 30min, Personal Training 60min)  
- **Weekly working rules** (Mon/Tue, 09:00–17:00 interval 30min)

### 4. Start development servers

```bash
php artisan serve
npm run dev
```

Visit: **http://localhost:8000**

---

## 🔌 API Endpoints

### Services
**GET /api/services**

### Availability
**GET /api/availability?date=YYYY-MM-DD&service_id=ID**

### Bookings
**POST /api/bookings**

### Working Rules (Admin)
**POST /api/working-rules**

---

## 🧪 Testing

```bash
php artisan test
```

---

## 📄 License

MIT License
