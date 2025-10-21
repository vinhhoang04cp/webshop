# 🛠️ Technology Stack

> **Purpose**: Quick reference for all technologies, versions, and dependencies

## 📋 Table of Contents
1. [Core Stack](#core-stack)
2. [Backend Technologies](#backend-technologies)
3. [Frontend Technologies](#frontend-technologies)
4. [Database & Cache](#database--cache)
5. [Development Environment](#development-environment)
6. [Testing Tools](#testing-tools)
7. [Version Matrix](#version-matrix)

---

## 🎯 Core Stack

### Runtime Environment
- **PHP**: 8.2+ (recommended 8.4)
- **Composer**: 2.x
- **Node.js**: 18.x or 20.x
- **npm**: 9.x or higher

### Framework
- **Laravel**: 12.x
- **Laravel Sanctum**: 4.0.14 (API authentication)

---

## ⚙️ Backend Technologies

### Core Framework & Libraries

| Package | Version | Purpose |
|---------|---------|---------|
| **laravel/framework** | ^12.0 | Main framework |
| **laravel/sanctum** | ^4.0.14 | API authentication |
| **laravel/sail** | ^2.0 | Docker development environment |
| **laravel/tinker** | ^2.10 | REPL for Laravel |
| **doctrine/dbal** | ^4.0 | Database abstraction layer |
| **guzzlehttp/guzzle** | ^7.9 | HTTP client |
| **intervention/image** | ^3.0 | Image manipulation |

### Development Dependencies

| Package | Version | Purpose |
|---------|---------|---------|
| **laravel/pint** | ^1.18 | Code style fixer |
| **fakerphp/faker** | ^1.24 | Generate fake data for testing |
| **phpunit/phpunit** | ^11.5 | Testing framework |
| **mockery/mockery** | ^1.6 | Mocking framework |
| **nunomaduro/collision** | ^8.5 | Error handler for CLI |

---

## 🎨 Frontend Technologies

### Build Tools
- **Vite**: 7.0.4 (Build tool & dev server)
- **Laravel Vite Plugin**: ^1.1

### CSS Framework
- **Tailwind CSS**: 4.0.15
  - Utility-first CSS framework
  - Custom configuration in `tailwind.config.js`

### JavaScript
- **Vanilla JavaScript**: ES6+
- **Axios**: HTTP client (if needed)

### Assets Structure
```
resources/
├── css/
│   └── app.css         # Main stylesheet with Tailwind
├── js/
│   └── app.js          # Main JavaScript entry
└── views/
    └── *.blade.php     # Blade templates
```

---

## 💾 Database & Cache

### Database
- **MySQL**: 8.0 (latest)
  - Main relational database
  - Strict mode enabled
  - Character set: utf8mb4
  - Collation: utf8mb4_unicode_ci

### Cache & Queue
- **Redis**: Alpine (latest)
  - Session storage
  - Cache driver
  - Queue driver
  - Pub/Sub for real-time features

### Database Management
- **phpMyAdmin** (Development only)
  - Port: 8080
  - Access: http://localhost:8080
  - Server: `mysql`

---

## 🐳 Development Environment

### Docker Services

```yaml
services:
  laravel.test:
    image: sail-8.4/app
    ports: ["80:80", "5173:5173"]
    depends_on: [mysql, redis]
    
  mysql:
    image: mysql:8.0
    ports: ["3306:3306"]
    environment:
      MYSQL_DATABASE: webshop
      MYSQL_USER: sail
      MYSQL_PASSWORD: password
    
  redis:
    image: redis:alpine
    ports: ["6379:6379"]
    
  phpmyadmin:
    image: phpmyadmin/phpmyadmin
    ports: ["8080:80"]
    depends_on: [mysql]
```

### Environment Files
- `.env`: Production/Development configuration
- `.env.example`: Template for new environments

### Key Configuration Files

| File | Purpose |
|------|---------|
| `compose.yaml` | Docker services definition |
| `vite.config.js` | Vite build configuration |
| `tailwind.config.js` | Tailwind CSS configuration |
| `phpunit.xml` | PHPUnit testing configuration |

---

## 🧪 Testing Tools

### Testing Framework
- **PHPUnit**: 11.5+
  - Unit tests in `tests/Unit/`
  - Feature tests in `tests/Feature/`

### Database Testing
- **In-memory SQLite**: For fast test execution
- **RefreshDatabase trait**: Reset database between tests

### Run Tests
```bash
# All tests
./vendor/bin/phpunit

# Specific test
./vendor/bin/phpunit tests/Feature/AuthTest.php

# With coverage (requires Xdebug)
./vendor/bin/phpunit --coverage-html coverage/
```

---

## 📊 Version Matrix

### Required Versions

| Technology | Minimum | Recommended | Notes |
|------------|---------|-------------|-------|
| **PHP** | 8.2 | 8.4 | Laravel 12 requirement |
| **MySQL** | 8.0 | 8.0 (latest) | For JSON functions |
| **Redis** | 6.0 | Alpine (latest) | For cache/queue |
| **Node.js** | 18.x | 20.x | For Vite 7.x |
| **Composer** | 2.0 | 2.x (latest) | PHP dependency manager |

### Framework Dependencies

```json
{
  "require": {
    "php": "^8.2",
    "laravel/framework": "^12.0",
    "laravel/sanctum": "^4.0.14",
    "doctrine/dbal": "^4.0",
    "guzzlehttp/guzzle": "^7.9"
  },
  "require-dev": {
    "fakerphp/faker": "^1.24",
    "laravel/pint": "^1.18",
    "phpunit/phpunit": "^11.5",
    "mockery/mockery": "^1.6"
  }
}
```

### Frontend Dependencies

```json
{
  "devDependencies": {
    "@tailwindcss/vite": "^4.0.15",
    "laravel-vite-plugin": "^1.1",
    "tailwindcss": "^4.0.15",
    "vite": "^7.0.4"
  }
}
```

---

## 🔧 Additional Tools

### Code Quality
- **Laravel Pint**: PSR-12 code style
  ```bash
  ./vendor/bin/pint
  ```

### API Development
- **Postman** (recommended): API testing
- **Insomnia** (alternative): API client

### Database Tools
- **TablePlus**: Database GUI (recommended)
- **DBeaver**: Open-source alternative
- **phpMyAdmin**: Web-based (included in Docker)

---

## 📦 Installation Summary

### Quick Setup
```bash
# 1. Clone repository
git clone <repository-url>
cd webshop

# 2. Install PHP dependencies
composer install

# 3. Install Node dependencies
npm install

# 4. Setup environment
cp .env.example .env
php artisan key:generate

# 5. Start Docker services
./vendor/bin/sail up -d

# 6. Run migrations & seeders
./vendor/bin/sail artisan migrate:fresh --seed

# 7. Build frontend assets
npm run dev
```

### URLs After Setup
- **Application**: http://localhost
- **phpMyAdmin**: http://localhost:8080
- **Vite Dev Server**: http://localhost:5173

---

## 🔗 External Resources

### Documentation
- [Laravel 12 Docs](https://laravel.com/docs/12.x)
- [Laravel Sanctum](https://laravel.com/docs/sanctum)
- [Tailwind CSS 4.0](https://tailwindcss.com/docs)
- [Vite](https://vite.dev)
- [MySQL 8.0](https://dev.mysql.com/doc/refman/8.0/en/)

### Learning Resources
- [Laravel Bootcamp](https://bootcamp.laravel.com)
- [Laracasts](https://laracasts.com)
- [Laravel News](https://laravel-news.com)

---

## 📚 Related Documentation

- **[GETTING_STARTED.md](./GETTING_STARTED.md)** - Installation & setup guide
- **[ARCHITECTURE.md](./ARCHITECTURE.md)** - System architecture
- **[CODING_CONVENTIONS.md](./CODING_CONVENTIONS.md)** - Code standards

---

**Cập nhật lần cuối**: 21/10/2025  
**Version**: 3.1 (Coupon System Added)  
**Author**: Hoàng Quang Vinh
- **Authorization**: Role-Based Access Control (RBAC)

