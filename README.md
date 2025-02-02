# Event Management System

[![PHP Version](https://img.shields.io/badge/PHP-8.0%2B-blue.svg)](https://www.php.net)
[![MySQL Version](https://img.shields.io/badge/MySQL-5.7%2B-blue.svg)](https://www.mysql.com)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![PSR-4 Compliant](https://img.shields.io/badge/PSR-4-orange.svg)](https://www.php-fig.org/psr/psr-4/)

A secure and scalable event management system built with PHP, featuring event management, event registration, event api and comprehensive dashboard analytics
with Event Attendees list csv download feature and ajax form submission for event
registration from user event dashboard.

## 📑 Table of Contents
- [Features](#-features)
- [Project Structure](#-project-structure)
- [Security Features](#-security-features)
- [Installation](#-installation)
- [Configuration](#️-configuration)
- [Usage](#-usage)
- [Development](#-development)
- [Contact](#-contact)
- [Acknowledgments](#-acknowledgments)

## 🌟 Features

- **User Authentication & Authorization**
  - Role-based access control (Admin, Attendee)
  - Secure password hashing
  - CSRF protection
  - Session management
  - Throttling
  - Rate limiting

- **Event Management**
  - Create, read, update, and delete events
  - Multiple event types (Free, Paid, User only, All user)
  - Registration deadline management
  - Capacity management

- **Registration System**
  - User and guest registration
  - Registration type control
  - Automatic capacity checking
  - Registration status tracking

- **Dashboard & Analytics**
  - Real-time event statistics
  - Registration trends
  - Event type distribution
  - Popular events tracking

## 🏗 Project Structure

<details>
<summary>Click to expand file structure</summary>

```bash
.
├── README.md
├── app
│   ├── Constants
│   │   ├── EventConstants.php
│   │   └── UserConstants.php
│   ├── Controllers
│   │   ├── Admin
│   │   │   ├── Auth
│   │   │   │   └── AuthController.php
│   │   │   ├── Dashboard
│   │   │   │   └── DashboardController.php
│   │   │   └── Event
│   │   │       ├── Attendee
│   │   │       │   └── EventAttendeeController.php
│   │   │       └── EventController.php
│   │   ├── Api
│   │   │   └── Events
│   │   │       └── EventsController.php
│   │   ├── Attendee
│   │   │   └── Auth
│   │   │       └── AuthController.php
│   │   └── Event
│   │       └── Dashboard
│   │           └── EventDashboardController.php
│   ├── Core
│   │   ├── Controller.php
│   │   ├── Logger.php
│   │   ├── Router.php
│   │   ├── Service.php
│   │   └── View.php
│   ├── Middleware
│   │   ├── Auth.php
│   │   ├── CsrfMiddleware.php
│   │   └── StoreIntendedUrl.php
│   ├── Models
│   │   ├── Permission.php
│   │   ├── Role.php
│   │   └── User.php
│   └── Services
│       ├── AdminDashboardService.php
│       ├── Auth
│       │   └── AuthService.php
│       ├── EventAttendeeService.php
│       ├── EventDashboardService.php
│       └── EventService.php
├── composer.json
├── composer.lock
├── config
│   └── database.php
├── database
│   └── event_management.sql
├── index.php
├── logs
│   └── site.log
├── project-structure.txt
├── public
│   └── assets
│       ├── css
│       │   ├── login.css
│       │   └── styles.css
│       ├── images
│       │   └── night-sky.jpg
│       └── js
│           ├── login.js
│           ├── main.js
│           └── register.js
├── routes
│   └── web.php
└── views
    ├── auth
    │   ├── admin
    │   │   ├── login.php
    │   │   └── register.php
    │   └── attendee
    │       ├── login.php
    │       └── register.php
    ├── dashboard.php
    ├── errors
    │   ├── 403.php
    │   ├── 404.php
    │   └── 500.php
    ├── event-attendees
    │   └── index.php
    ├── event-dashboard
    │   └── index.php
    ├── events
    │   ├── create.php
    │   ├── edit.php
    │   └── index.php
    ├── includes
    │   ├── footer.php
    │   ├── head-css.php
    │   ├── header.php
    │   ├── navbar.php
    │   ├── pagination.php
    │   ├── sidebar.php
    │   └── vendor-scripts.php
    └── layouts
        ├── master.php
        └── public.php
```
</details>

## 🔒 Security Features

1. **SQL Injection Prevention**
   - Prepared statements
   - Parameter binding
   - Input validation

2. **XSS Protection**
   - HTML escaping
   - Content Security Policy
   - Input sanitization

3. **CSRF Protection**
   - Token validation
   - Same-site cookies
   - Request validation

4. **Authentication Security**
   - Secure password hashing
   - Session management
   - Rate limiting

## 🚀 Installation

1. **Clone the repository**

```bash
git clone https://github.com/mahadinext/event-management.git
cd event-management
```

2. **Install dependencies**

```bash
# Install dependencies
composer install

# Generate autoload files
composer dump-autoload
```

3. **Configure database**
   - Update database credentials in `config/database.php`

4. **Import database schema**

```bash
mysql -u root -p event_management < database/event_management.sql
```

5. **Set up permissions**

```bash
chmod 755 -R public/
chmod 755 -R logs/
```

6. **Run the application**

```bash
php -S localhost:8080 -t public
```

## ⚙️ Configuration

1. **Database Configuration**
   - Update database credentials in `config/database.php`

2. **Application Configuration**
   - Set up logging configuration
   - Configure session handling

## 📝 Usage

1. **Admin Panel**
   - Access `/admin/login`
   - Dashboard: `/admin/dashboard`
   - Default credentials:
     - Username: admin@admin.com
     - Password: Secured@25@

2. **Event Management**
   - Create events: `/admin/events/create`
   - View events: `/admin/events`
   - Manage registrations: `/admin/event-attendees`

3. **User Panel**
   - Register: `/attendee/register`
   - Login: `/attendee/login`
   - View events: `/`
   - Default credentials:
     - Username: attendee@attendee.com
     - Password: Secured@25@

4. **API**
   - Get all events: `/api/events`
   - Get specific event details: `/api/events/{id}`

## 🔧 Development

1. **Coding Standards**
   - PSR-4 autoloading
   - PSR-12 coding style
   - Object-oriented programming principles

2. **Best Practices**
   - Service layer pattern
   - Dependency injection
   - Single responsibility principle
   - DRY (Don't Repeat Yourself)

3. **Error Handling**
   - Comprehensive error logging
   - User-friendly error pages
   - Debug mode configuration

## 📫 Contact

Md. Mahadi Islam
- GitHub: [@mahadinext](https://github.com/mahadinext)
- LinkedIn: [Md. Mahadi Islam](https://linkedin.com/in/md-mahadi-islam-a74973288/)
- Email: mahediihasan220@gmail.com

Project Link: [https://github.com/mahadinext/event-management](https://github.com/mahadinext/event-management)

## 🙏 Acknowledgments

* [PHP](https://www.php.net/) - The core language used
* [MySQL](https://www.mysql.com/) - Database system
* [Bootstrap](https://getbootstrap.com/) - Frontend framework
* [Chart.js](https://www.chartjs.org/) - For interactive charts
* [DataTables](https://datatables.net/) - For enhanced table functionality
* [Font Awesome](https://fontawesome.com/) - For icons
* [jQuery](https://jquery.com/) - JavaScript library
* [Composer](https://getcomposer.org/) - Dependency management
