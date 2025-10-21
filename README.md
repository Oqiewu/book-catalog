# Каталог книг - Тестовое задание на Yii2

Web-приложение для управления каталогом книг с подпиской на уведомления о новых книгах авторов.

## Возможности

- **CRUD книг и авторов** (авторизованные пользователи)
- **Просмотр каталога** (все пользователи, включая гостей)
- **Подписка на авторов** с SMS-уведомлениями при добавлении новых книг
- **Отчет ТОП-10 авторов** по количеству книг за выбранный год
- **Загрузка обложек книг** в MinIO (S3-compatible storage)
- **Связь многие-ко-многим** между книгами и авторами

## Технологии

- **PHP 8.0+** / Yii2 Framework
- **MySQL 8.0**
- **MinIO** (S3-compatible object storage)
- **Docker Compose**
- **Bootstrap 5**
- **SMS API**: smspilot.ru (ключ-эмулятор)

## Быстрый старт

### 1. Запуск

```bash
docker-compose up -d
docker exec -it book-catalog-app composer install
docker exec -it book-catalog-app php yii migrate
```

### 2. Открыть в браузере

- **Приложение**: http://localhost:8080
- **MinIO Console**: http://localhost:9001 (minioadmin/minioadmin)

### 3. Войти в систему

- **Логин**: `admin`
- **Пароль**: `admin`

## Структура БД

```sql
authors         # Авторы (ФИО)
books           # Книги (название, год, описание, ISBN, обложка)
book_author     # Связь книг и авторов (many-to-many)
subscriptions   # Подписки на авторов (email или телефон)
```

## Права доступа

| Действие | Гость | Авторизованный |
|----------|-------|----------------|
| Просмотр книг/авторов | ✅ | ✅ |
| Подписка на автора | ✅ | ✅ |
| Просмотр отчета ТОП-10 | ✅ | ✅ |
| Добавление книг/авторов | ❌ | ✅ |
| Редактирование/удаление | ❌ | ✅ |

## Архитектура

Проект следует принципам **DRY, KISS, SOLID, YAGNI**.

### Паттерны проектирования

1. **MVC** - базовая архитектура Yii2
2. **Service Layer** - бизнес-логика в сервисах:
   - `BookService` - управление книгами + уведомления
   - `StorageService` - работа с MinIO/S3
   - `SubscriptionService` - управление подписками
   - `SmsService` - отправка SMS через smspilot.ru
3. **Dependency Injection** - внедрение сервисов в контроллеры
4. **Active Record** - ORM для работы с БД
5. **Adapter** - StorageService как адаптер для AWS S3 SDK

### Структура

```
├── controllers/      # BookController, AuthorController, ReportController
├── models/           # Author, Book, Subscription, BookAuthor
├── services/         # BookService, StorageService, SubscriptionService, SmsService
├── views/            # Представления для всех контроллеров
├── migrations/       # Миграции БД
└── config/           # Конфигурация приложения
```

## Особенности реализации

### MinIO для хранения обложек

Вместо локального хранилища файлов используется **MinIO** (S3-compatible):
- Bucket `book-covers` создается автоматически
- Публичный доступ к изображениям через URL
- Легкая миграция на AWS S3 в продакшене
- Использует AWS SDK для PHP

### SMS-уведомления

При добавлении книги автоматически отправляются SMS всем подписчикам авторов:
- API: smspilot.ru
- Используется ключ-эмулятор (реальная отправка не происходит)
- Нормализация телефонных номеров к формату +7XXXXXXXXXX

## Команды для работы

### Docker

```bash
# Запуск
docker-compose up -d

# Остановка
docker-compose down

# Остановка с удалением данных
docker-compose down -v

# Логи
docker logs book-catalog-app
```

### Миграции

```bash
# Применить миграции
docker exec -it book-catalog-app php yii migrate

# Откатить последнюю миграцию
docker exec -it book-catalog-app php yii migrate/down
```

### Тестирование

```bash
# Все тесты
docker exec -it book-catalog-app vendor/bin/codecept run

# Только unit
docker exec -it book-catalog-app vendor/bin/codecept run unit

# С покрытием
docker exec -it book-catalog-app vendor/bin/codecept run --coverage
```

## Конфигурация (.env)

```env
# App
APP_PORT=8081
APP_DEBUG=true

# Database
DB_HOST=db
DB_PORT=3306
DB_DATABASE=book_catalog
DB_USERNAME=root
DB_PASSWORD=root

# MinIO
MINIO_ROOT_USER=minioadmin
MINIO_ROOT_PASSWORD=minioadmin
MINIO_ENDPOINT=http://minio:9000
MINIO_BUCKET=book-covers
```

## Валидация

### Книги
- Название: обязательно, макс 255 символов
- Год: обязательно, 1000-9999
- ISBN: уникальный, только цифры/дефисы/X
- Обложка: jpg/jpeg/png/gif, макс 2МБ
- Авторы: хотя бы один

### Авторы
- Фамилия, Имя: обязательно
- Отчество: необязательно

### Подписки
- Email или телефон: хотя бы одно обязательно
- Уникальность: автор + email

## Локальная установка (без Docker)

```bash
# 1. Установить зависимости
composer install

# 2. Настроить .env с параметрами локальной БД
# 3. Создать БД MySQL
# 4. Применить миграции
php yii migrate

# 5. Запустить сервер
php yii serve

# Открыть http://localhost:8080
```

**Примечание**: Для работы с MinIO без Docker потребуется локальная установка MinIO или настройка AWS S3.
