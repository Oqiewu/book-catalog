# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a **Yii 2 Basic Project Template** configured as a book catalog application. It uses PHP 7.4+ and the Yii 2 framework (~2.0.45) with Bootstrap 5 for the frontend.

## Development Principles

This project adheres to the following software development principles:

- **DRY (Don't Repeat Yourself)**: Avoid code duplication. Extract common logic into reusable functions, methods, or components.
- **KISS (Keep It Simple, Stupid)**: Prefer simple, straightforward solutions over complex ones. Code should be easy to read and understand.
- **SOLID**:
  - **S**ingle Responsibility Principle: Each class should have one responsibility
  - **O**pen/Closed Principle: Classes should be open for extension but closed for modification
  - **L**iskov Substitution Principle: Derived classes must be substitutable for their base classes
  - **I**nterface Segregation Principle: Clients should not depend on interfaces they don't use
  - **D**ependency Inversion Principle: Depend on abstractions, not concretions
- **YAGNI (You Aren't Gonna Need It)**: Don't implement functionality until it's actually needed. Avoid premature optimization and over-engineering.

## Development Environment

### Docker Setup (Recommended)

The project uses Docker Compose with four services:
- **app**: PHP-FPM container (port configured via `APP_PORT` in `.env`)
- **nginx**: Web server (port 8080)
- **db**: MySQL 8.0 database (port 3307 mapped from 3306)
- **minio**: S3-compatible object storage (ports 9000 API, 9001 Console)

**Start the application:**
```bash
docker-compose up -d
```

**Stop the application:**
```bash
docker-compose down
```

**Access the application:**
- Via nginx: http://127.0.0.1:8080
- Via app container: http://127.0.0.1:8081 (or configured `APP_PORT`)
- MinIO Console: http://127.0.0.1:9001 (admin: minioadmin/minioadmin)

### Database Configuration

Database credentials are managed via `.env` file and loaded through `vlucas/phpdotenv`. The connection configuration is in `config/db.php` and uses environment variables:
- `DB_HOST=db` (Docker service name)
- `DB_PORT=3306`
- `DB_DATABASE=book_catalog`
- `DB_USERNAME=root`
- `DB_PASSWORD=root`

### MinIO Configuration

Object storage for book covers is managed via `.env` and uses MinIO (S3-compatible):
- `MINIO_ROOT_USER=minioadmin`
- `MINIO_ROOT_PASSWORD=minioadmin`
- `MINIO_ENDPOINT=http://minio:9000`
- `MINIO_BUCKET=book-covers`

The `StorageService` automatically creates the bucket on first use.

## Common Commands

### Composer

**Install dependencies:**
```bash
composer install
```

**Update dependencies:**
```bash
composer update
```

### Testing

Tests are built with **Codeception** and configured in `codeception.yml`.

**Run all tests (unit + functional):**
```bash
vendor/bin/codecept run
```

**Run specific test suites:**
```bash
vendor/bin/codecept run unit
vendor/bin/codecept run functional
vendor/bin/codecept run unit,functional
```

**Run with code coverage:**
```bash
vendor/bin/codecept run --coverage --coverage-html --coverage-xml
```

Coverage output is saved to `tests/_output/`.

**Note:** Acceptance tests require additional setup (Selenium) and are disabled by default. See README.md for full acceptance test setup.

### Console Commands (Yii CLI)

The project includes a console application accessed via the `./yii` script.

**Run console commands:**
```bash
./yii <command>
```

**Example console controller:**
- `commands/HelloController.php` - sample console command

**For testing environment:**
```bash
tests/bin/yii <command>
```

**Run test server:**
```bash
tests/bin/yii serve
```

**Run migrations:**
```bash
./yii migrate
```

**Note:** There is currently no `migrations/` directory in the project, but migrations can be created and run using standard Yii 2 migration commands.

## Architecture & Structure

### MVC Pattern

The application follows Yii 2's MVC architecture:

- **Models** (`models/`): Business logic and data
  - `User.php` - User identity model
  - `LoginForm.php` - Login form model
  - `ContactForm.php` - Contact form model

- **Controllers** (`controllers/`): Handle HTTP requests
  - `SiteController.php` - Main controller with actions: index, login, logout, contact, about

- **Views** (`views/`): Presentation layer
  - `layouts/` - Page layouts
  - `site/` - View files for SiteController

### Web vs Console Applications

The project has two entry points:

1. **Web Application** (`web/index.php`)
   - Config: `config/web.php`
   - User authentication enabled
   - Mailer configured (uses file transport by default)
   - Debug and Gii modules available in dev environment

2. **Console Application** (`yii`)
   - Config: `config/console.php`
   - Controller namespace: `app\commands`
   - Used for background tasks, migrations, and CLI operations

### Environment-Specific Configuration

- `YII_ENV_DEV`: Enables debug toolbar and Gii code generator
- Debug module: `yii\debug\Module`
- Gii module: `yii\gii\Module` (code generation tool)

Configuration files:
- `config/web.php` - Web application config
- `config/console.php` - Console application config
- `config/db.php` - Database connection (uses environment variables)
- `config/params.php` - Application parameters
- `config/test.php` - Test environment config
- `config/test_db.php` - Test database config

### Assets and Frontend

- **Assets** (`assets/`): Asset bundles for CSS/JS
  - `AppAsset.php` - Main application asset bundle

- **Web Root** (`web/`): Public files, entry script
  - `web/assets/` - Published asset files (writable)
  - `web/uploads/` - Legacy directory (now using MinIO)

- **Frontend Framework**: Bootstrap 5 (`yiisoft/yii2-bootstrap5`)

### File Storage

Book cover images are stored in **MinIO** (S3-compatible object storage):
- **Service**: `StorageService` - handles all file operations
- **Bucket**: `book-covers` (auto-created)
- **SDK**: AWS SDK for PHP v3
- **Public URLs**: `http://localhost:9000/book-covers/{filename}`

**Advantages of MinIO**:
- S3 API compatibility
- Easy migration to AWS S3 in production
- Separation of concerns (storage isolated from application)
- Scalability

### Other Directories

- **commands/**: Console controllers
- **mail/**: Email view templates
- **runtime/**: Generated files, logs, cache (writable)
- **services/**: Business logic services
  - `BookService.php` - Book management and notifications
  - `StorageService.php` - MinIO/S3 file storage
  - `SubscriptionService.php` - Subscription management
  - `SmsService.php` - SMS notifications via smspilot.ru
- **tests/**: Test suites (unit, functional, acceptance)
- **widgets/**: Reusable UI widgets
  - `Alert.php` - Alert widget

## Development Workflow

1. **Database changes**: Create migrations in `migrations/` directory
2. **New features**:
   - Add models in `models/`
   - Create controllers in `controllers/`
   - Add views in `views/<controller-name>/`
3. **Background tasks**: Create console controllers in `commands/`
4. **Tests**: Write tests in appropriate `tests/` subdirectory
5. **Environment config**: Update `.env` for environment-specific settings

## Important Notes

- Cookie validation key is auto-generated by Composer post-install hook
- File-based mailer transport is enabled by default (emails saved to `runtime/mail/`)
- Pretty URLs are commented out in `config/web.php` - uncomment `urlManager` to enable
- Composer uses `asset-packagist.org` for Bower/NPM assets
- Runtime and web/assets directories must be writable (0777)
