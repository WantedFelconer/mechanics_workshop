# Car Workshop Appointment System

A web-based appointment booking system for a car repair workshop. Customers can view real-time mechanic availability and book appointments. Admins can manage bookings through a secure dashboard.

## Features

- **Real-time availability** – View open slots per mechanic with color-coded status indicators
- **Online booking** – Appointment form with client & vehicle details, duplicate and capacity checks
- **Admin dashboard** – Session-protected panel with stats, inline editing, and appointment deletion
- **Server + client validation** – Dual-layer input validation (PHP + JavaScript)

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | PHP (vanilla, no framework) |
| Frontend | HTML5, CSS3, Vanilla JavaScript |
| Database | MySQL (via MySQLi) |

## Setup

1. **Database** – Run `appointment.sql` against your MySQL server. Creates the `car_workshop` database with `mechanics`, `admins`, and `appointments` tables.
2. **Configure** – Edit `db.php` if your MySQL credentials differ from the defaults (host: `localhost`, user: `root`, pass: empty, db: `car_workshop`).
3. **Serve** – Deploy all files to your web server's document root.

**Default admin login:**

| Username | Password |
|----------|----------|
| admin | admin123 |

## Structure

```
├── css/style.css           # Dark-themed responsive stylesheet
├── js/script.js            # AJAX, dynamic UI, client-side validation
├── index.php               # Public booking page
├── admin.php               # Admin panel (session-protected)
├── login.php               # Admin login page
├── logout.php              # Session destroy & redirect
├── api_availability.php    # JSON API for mechanic availability
├── db.php                  # Database connection + session start
└── appointment.sql         # Schema + seed data
```

## Requirements

- PHP 7.x or 8.x with `mysqli` extension
- MySQL / MariaDB
- Web server (Apache, Nginx, or PHP's built-in server)
