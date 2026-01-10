# Cinema Diploma

Инструкция по запуску проекта локально.

## Требования

- PHP 8.2+
- Composer
- Node.js 18+
- npm

## Быстрый старт

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --force
npm install
npm run build
```

## Запуск проекта

```bash
php artisan serve
```

В другом окне терминала:

```bash
npm run dev
```

## Полезные команды

- Запуск тестов:

```bash
composer test
```

- Сборка фронтенда:

```bash
npm run build
```

## Примечания

- По умолчанию используется SQLite. Файл базы данных можно создать командой:

```bash
touch database/database.sqlite
```

- Если используете другую БД, настройте подключение в `.env`.
