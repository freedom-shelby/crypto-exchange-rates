# Crypto Exchange Rates - Symfony Docker Application

A Symfony web application for crypto exchange rates tracking, running in Docker containers with PHP, MySQL, and Redis.

## 🚀 Quick Start

### Prerequisites

- Docker Desktop installed on Windows
- Git

### Installation

1. **Clone the repository:**

```bash
git clone https://github.com/freedom-shelby/crypto-exchange-rates.git
cd crypto-exchange-rates
```

2. **Start the application:**

```bash
docker-compose up --build -d
```

3. **Install Symfony dependencies:**

```bash
docker-compose exec php composer install
```

4. **Set up the database:**

```bash
docker-compose exec php php bin/console doctrine:database:create --if-not-exists
```
```bash
docker-compose exec php php bin/console doctrine:migrations:migrate --no-interaction
```
```bash
docker-compose exec php php bin/console doctrine:fixtures:load
```

5. **Start the exchange rate collection:**

```bash
# Manual update (test)
docker-compose exec php php bin/console app:update-exchange-rates

# Start automatic updates (every 5 minutes)
docker-compose exec php php bin/console messenger:consume scheduler_default -v
```

6. **Access the application:**

- **Main App:** http://localhost:8080

## 📊 API Endpoints

- **Postman Documentation:** https://documenter.getpostman.com/view/580659/2sB3QDwszo

#### 1. ~/api/rates/last-24h Requests

```
GET /api/rates/last-24h?pair=EUR/BTC HTTP/1.1
Host: localhost:8080
```

#### 1. ~/api/rates/last-24h Response

```
{
  "success": true,
  "data": {
    "pair": "EUR/BTC",
    "period": "last-24h",
    "rates": [
      {
        "id": 1,
        "pair": "EUR/BTC",
        "base_currency": {
          "code": "EUR",
          "name": "Euro",
          "symbol": "€"
        },
        "quote_currency": {
          "code": "BTC",
          "name": "Bitcoin",
          "symbol": "₿"
        },
        "rate": 94379.87,
        "timestamp": "2025-09-28 20:00:52",
        "unix_timestamp": 1759089652
      }
    ],
    "count": 1
  },
  "meta": {
    "generated_at": "2025-09-28 21:45:39",
    "timezone": "UTC"
  }
}

```

#### 2. ~/api/rates/day Requests

```
GET /api/rates/day?pair=EUR/BTC&date=2025-09-28 HTTP/1.1
Host: localhost:8080
```

#### 2. ~/api/rates/day Response

```
{
  "success": true,  
  "data": {
    "pair": "EUR/BTC",
    "period": "last-24h",
    "rates": [
      {
        "id": 1,
        "pair": "EUR/BTC",
        "base_currency": {
          "code": "EUR",
          "name": "Euro",
          "symbol": "€"
        },
        "quote_currency": {
          "code": "BTC",
          "name": "Bitcoin",
          "symbol": "₿"
        },
        "rate": 94379.87,
        "timestamp": "2025-09-28 20:00:52",
        "unix_timestamp": 1759089652
      }
    ],
    "count": 1
  },
  "meta": {
    "generated_at": "2025-09-28 21:45:39",
    "timezone": "UTC"
  }
}
```

## 🛠️ Development

### ToDo List
- Add a **caching system** with redis DB
- Add **unit-tests**
- Normalize DB structure (add `currency_pairs` table with relations to `currencies and `exchange_rates` tables)
- Add more **currency pairs** and **providers**

### Project Structure

```
crypto-exchange-rates/
├── docker-compose.yml              # Docker services configuration
├── services/
│   └── php/
│       ├── Dockerfile              # PHP 8.2 + Apache container
│       ├── php.ini                # PHP settings with Xdebug
│       └── apache-symfony.conf     # Apache virtual host config
├── src/                           # Symfony application code
│   ├── src/
│   │   ├── Controller/            # API controllers
│   │   │   
│   │   ├── Entity/               # Doctrine entities
│   │   │   
│   │   ├── Repository/           # Database repositories
│   │   │   
│   │   ├── Service/              # Business logic services
│   │   │   
│   │   └── Command/              # CLI commands
│   │       
│   ├── migrations/               # Database migrations
│   ├── config/                  # Symfony configuration
│   └── tests/                   # PHPUnit tests
├── logs/                        # Application and debug logs
└── mysql/init/                 # Database initialization scripts
```

### Services

- **PHP 8.2** with Apache
    - Symfony 7.3 framework
    - Doctrine ORM with MySQL
    - HTTP Client for Binance API
    - Monolog for logging
    - Symfony Scheduler for cron jobs
    - Xdebug for debugging

- **MySQL 8.0**
    - Database: `crypto_db`
    - User: `crypto_user`
    - Password: `crypto_password`

- **Redis 7**
    - Caching and session storage

- **phpMyAdmin**
    - Database management interface

### Development Commands

```bash
# Start services
docker-compose up -d

# View logs
docker-compose logs -f php

# Access PHP container
docker-compose exec php bash

# Run Symfony commands
docker-compose exec php php bin/console cache:clear
docker-compose exec php php bin/console make:controller

# Install new packages
docker-compose exec php composer require package-name

# Run tests
docker-compose exec php php bin/phpunit

# Stop services
docker-compose down
```

### Debugging with Xdebug

**VS Code Setup:**

1. Install "PHP Debug" extension
2. Create `.vscode/launch.json`:

```json
{
  "version": "0.2.0",
  "configurations": [
    {
      "name": "Listen for Xdebug",
      "type": "php",
      "request": "launch",
      "port": 9003,
      "pathMappings": {
        "/var/www/html": "${workspaceFolder}/src"
      }
    }
  ]
}
```

**PhpStorm Setup:**

- Set Xdebug port to `9003`
- Configure path mapping: `/var/www/html` → `crypto-exchange-rates/src`

### Database Configuration

The application connects to MySQL using these environment variables in `src/.env`:

```
DATABASE_URL="mysql://crypto_user:crypto_password@mysql:3306/crypto_rates?serverVersion=8.0"
REDIS_URL=redis://redis:6379
```

## 📝 Environment Variables

- `APP_ENV=dev` for development
- `APP_SECRET` - Generate a secure secret key
- Database and Redis URLs are pre-configured for Docker

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🐛 Troubleshooting

### Common Issues

**Symfony Flex Error:**

```bash
docker-compose exec php composer clear-cache
docker-compose exec php rm -rf vendor composer.lock
docker-compose exec php composer install
```

**Permission Issues:**

```bash
docker-compose exec php chown -R www-data:www-data var/
```

**Port Already in Use:**

- Change ports in `docker-compose.yml` if 8080, 3306, or 6379 are occupied

### Logs Location

- Application logs: `logs/`
- Xdebug logs: `logs/xdebug.log`
- Apache logs: Inside container at `/var/log/apache2/`

## 📞 Support

If you encounter any issues, please check the logs first:

```bash
docker-compose logs php
docker-compose logs mysql
```

Create an issue on GitHub if you need help!