# Subscription Platform API

A Laravel REST API for subscribing users to websites and notifying them by email whenever a new post is published. The application is API-only: it does not include authentication or a frontend.

## Requirements

- PHP 8.2+ (compatible with the required PHP 7.* or 8.* range)
- Laravel 12
- Composer
- MySQL 8.0+ (or a compatible MySQL server)
- A mail service, or the local `log` mail driver for development

## Features

- Multiple websites can be managed in the system.
- Users can subscribe to a particular website.
- Posts can be created for a particular website.
- A console command checks all websites for new posts and queues email notifications.
- Queue workers send notifications in the background.
- Each subscriber receives a given post only once, even if the command is run repeatedly.
- Validation prevents invalid websites, posts, email addresses, and duplicate subscriptions.
- No authentication is required by the API.

## Installation

1. Clone the repository:

   ```bash
   git clone https://github.com/ahmedabdelaziz00/test.git
   cd test
   ```

2. Install PHP dependencies:

   ```bash
   composer install
   ```

3. Create the environment file and application key:

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. Create a MySQL database, then configure `.env`:

   ```dotenv
   APP_NAME="Subscription Platform"
   APP_ENV=local
   APP_DEBUG=true
   APP_URL=http://localhost

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=subscription_platform
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. Configure mail delivery. For local development, emails can be written to the Laravel log:

   ```dotenv
   MAIL_MAILER=log
   MAIL_FROM_ADDRESS="hello@example.com"
   MAIL_FROM_NAME="${APP_NAME}"
   ```

   For a real SMTP provider, replace `MAIL_MAILER` and provide the corresponding host, port, username, password, and encryption settings.

6. Run the migrations and optional seeders:

   ```bash
   php artisan migrate --seed
   ```

7. Start the API:

   ```bash
   php artisan serve
   ```

   The API will be available at `http://localhost:8000`.

## API usage

All endpoints accept and return JSON. No bearer token or other authentication is required.

### Create a post for a website

```http
POST /api/websites/{website}/posts
Content-Type: application/json

{
  "title": "Example post",
  "description": "The post description."
}
```

The website must exist, and both `title` and `description` are required. A successful request creates the post; notifications are sent by the queue workflow described below.

### Subscribe a user to a website

```http
POST /api/websites/{website}/subscriptions
Content-Type: application/json

{
  "email": "subscriber@example.com"
}
```

The website must exist, the email address must be valid, and the same email cannot be subscribed to the same website more than once.

Validation failures return an appropriate `4xx` response with JSON validation errors. Successful create requests return a `2xx` response containing the created resource.

## Sending notifications

Run the notification command to find all new posts across all websites and queue email jobs for subscribers who have not previously received those posts:

```bash
php artisan subscriptions:send-new-posts
```

Start a queue worker in a separate terminal so queued notifications are processed:

```bash
php artisan queue:work
```

The command is safe to run repeatedly. A delivery record is used to ensure that no duplicate story is sent to a subscriber. With the database queue driver, the queue tables must also be migrated before starting the worker.

For scheduled delivery, configure the command in the application's scheduler and run Laravel's scheduler process according to the deployment environment.

## Testing

Run the automated test suite with:

```bash
php artisan test
```

## Project conventions

- Database schema is managed through Laravel migrations.
- Website, post, subscriber, and notification-delivery data is persisted in MySQL.
- Email delivery is handled by queued jobs rather than blocking API requests.
- The API contains no frontend pages and does not require authentication.

## Troubleshooting

- If emails do not appear in a mailbox while using `MAIL_MAILER=log`, inspect `storage/logs/laravel.log`.
- If notifications remain queued, confirm that `php artisan queue:work` is running and that the configured queue connection is available.
- If migrations fail, verify the MySQL credentials and ensure the configured database already exists.

## License

This project is licensed under the [MIT License](https://opensource.org/licenses/MIT).
