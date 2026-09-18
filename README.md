# Subscription Platform API

A simple RESTful API (Laravel + MySQL) where users can subscribe to websites and receive an
email whenever a new post is published on a website they're subscribed to. No authentication
is required — subscription is done directly by email address.

## Tech Stack
- PHP 8.2+
- Laravel 12
- MySQL 8+
- Queues (`database` driver by default)

## 1. Setup

```bash
# 1. Clone and install dependencies
git clone https://github.com/ahmedabdelaziz00/test.git
cd test
composer install

# 2. Environment
cp .env.example .env
php artisan key:generate
```

Edit `.env` and set MySQL credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=subscription_platform
DB_USERNAME=root
DB_PASSWORD=

QUEUE_CONNECTION=database
MAIL_MAILER=log   # emails are written to storage/logs/laravel.log instead of being sent for real
```

```bash
# 3. Create the database, then run migrations + seeders
php artisan migrate --seed

# 4. Serve the app
php artisan serve
```

## 2. Sending Emails (Queue Worker)

New posts are queued for delivery automatically (via an event listener) as soon as they're
created. You need a running queue worker for them to actually go out:

```bash
php artisan queue:work
```

There's also a standalone Artisan command that scans **all** websites and queues emails for
**any** post that hasn't been sent yet to a given subscriber (catch-up / safety net, useful if
the worker was down or a job failed):

```bash
php artisan emails:send
```

You can schedule this command to run periodically (e.g. every 5 minutes) via
`routes/console.php` using Laravel's scheduler, then run:

```bash
php artisan schedule:work
```

## 3. Running Tests

```bash
php artisan test
```

## 4. API Endpoints

Base URL: `/api/v1`

All responses are JSON with the shape:
```json
{ "status": "success" | "error", "data": ..., "message": "..." }
```

### Websites

| Method | Endpoint         | Description         |
|--------|------------------|----------------------|
| GET    | `/websites`      | List all websites    |

**GET `/api/v1/websites`**
```json
{
  "status": "success",
  "data": [
    { "id": 1, "name": "Tech Blog", "url": "https://techblog.example.com" }
  ]
}
```

---

### Posts

| Method | Endpoint                              | Description                          |
|--------|----------------------------------------|---------------------------------------|
| GET    | `/websites/{website}/posts`            | List all posts for a website          |
| GET    | `/websites/{website}/posts/{post}`     | Get a single post                     |
| POST   | `/websites/{website}/posts`            | Create a post (triggers emails)       |

**POST `/api/v1/websites/{website}/posts`**

Body:
```json
{
  "title": "We just launched v2!",
  "description": "Here is everything that's new in this release..."
}
```

| Field       | Rules                        |
|-------------|-------------------------------|
| `title`     | required, string, max:255     |
| `description` | required, string           |

Response `201`:
```json
{
  "status": "success",
  "data": {
    "id": 10,
    "website_id": 1,
    "title": "We just launched v2!",
    "description": "Here is everything that's new in this release..."
  }
}
```

Creating a post automatically dispatches an email (queued) to every current subscriber of
that website.

Response `404` if `{website}` doesn't exist.

---

### Subscriptions

| Method | Endpoint                                   | Description                       |
|--------|----------------------------------------------|------------------------------------|
| POST   | `/websites/{website}/subscribe`               | Subscribe an email to a website    |
| DELETE | `/websites/{website}/unsubscribe`             | Unsubscribe an email from a website|
| GET    | `/websites/{website}/subscribers`             | List a website's subscribers       |

**POST `/api/v1/websites/{website}/subscribe`**

Body:
```json
{
  "name": "Ahmed Abdelaziz",
  "email": "ahmed@example.com"
}
```

| Field   | Rules                          |
|---------|----------------------------------|
| `name`  | required, string, max:255        |
| `email` | required, valid email, max:255   |

- `201` — subscribed successfully.
- `409` — this email is already subscribed to this website.
- `404` — `{website}` doesn't exist.

If the email hasn't been seen before, a `User` record is created automatically (no password /
registration flow — the platform has no authentication).

**DELETE `/api/v1/websites/{website}/unsubscribe`**

Body:
```json
{ "email": "ahmed@example.com" }
```

- `200` — unsubscribed successfully.
- `404` — user not found, or user is not subscribed to this website.

**GET `/api/v1/websites/{website}/subscribers`**

Response:
```json
{
  "status": "success",
  "data": [
    { "id": 3, "name": "Ahmed Abdelaziz", "email": "ahmed@example.com" }
  ]
}
```

> **Note:** since there's no authentication, this endpoint returns subscriber PII (names and
> emails) to anyone who can guess a website id. That's acceptable for this assessment's brief,
> but in a real deployment this endpoint (and `subscribe`/`unsubscribe`) should sit behind auth
> and rate limiting.

## 5. Design Notes

- **No duplicate emails**: enforced at three layers — a unique DB constraint on
  `sent_emails(post_id, user_id)`, a check inside the queued job before sending, and the
  `emails:send` command only queuing pairs that don't already have a `sent_emails` row.
- **Two delivery paths**: an `PostPublished` event + listener queues emails immediately when a
  post is created; the `emails:send` command is a separate catch-up sweep across all websites,
  satisfying the brief's requirement independently of the event path.
- **Queued delivery**: all email sending happens in `SendPostEmailJob` (`ShouldQueue`), never
  synchronously in a request/response cycle.
