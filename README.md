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
docker-compose exec php php bin/console doctrine:migrations:migrate --no-interaction
```

5. **Access the application:**
- **Main App:** http://localhost:8080
- **phpMyAdmin:** http://localhost:8081
- **Symfony Profiler:** http://localhost:8080/_profiler (dev mode)

## 🛠️ Development

### Project Structure
```
crypto-exchange-rates/
├── docker-compose.yml          # Docker services configuration
├── services/
│   └── php/
│       ├── Dockerfile          # PHP container configuration
│       ├── php.ini            # PHP settings with Xdebug
│       └── apache-symfony.conf # Apache virtual host config
├── src/                       # Symfony application code
├── logs/                      # Application and debug logs
└── mysql/init/               # Database initialization scripts
```

### Services

- **PHP 8.2** with Apache
    - Symfony framework
    - Xdebug for debugging
    - Redis and MySQL extensions
    - Composer

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