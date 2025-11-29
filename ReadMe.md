```markdown
# Libiverse Backend (Laravel API)

**Libiverse** – A private social platform for British Council Library members in Myanmar. Members can discuss books, join reading challenges, RSVP to events, follow each other, and build a vibrant reading community.

This is the complete **Laravel 11 API-only backend** that powers the entire Libiverse platform.

## Features

- Secure registration + admin approval workflow
- Role system: Admin, Moderator, Member
- Full forum system (public/private forums, threaded discussions, pinning, locking)
- Book management with Google Books API integration & manual entry
- Media uploads (images, videos, PDFs) with automatic video thumbnails
- Event system with RSVP (going / interested / not going)
- Gamified reading challenges with progress tracking & badges
- Notifications (likes, replies, mentions, follows, reports, join requests)
- Follow system + personal activity feed
- Post reporting & moderation dashboard
- Private forum join requests & approvals
- GDPR-compliant (public UUIDs, account disabling, no direct IDs exposed)
- Modular, clean, service-oriented architecture

## Tech Stack

- Laravel 11 (API-only)
- Laravel Sanctum (JWT token auth)
- MySQL
- Google Books API
- ApyHub API (video thumbnails)
- Custom modular structure with `php artisan make:module`

## Prerequisites

- PHP ≥ 8.2
- Composer
- MySQL / MariaDB / PostgreSQL
- Git

## Setup Instructions (Step-by-Step)

### 1. Clone the Repository

```bash
git clone https://github.com/yourusername/libiverse-backend.git
cd libiverse-backend
```

### 2. Install PHP Dependencies

```bash
composer install
```

If you get Doctrine/DBAL errors (needed for enum migrations):

```bash
composer require doctrine/dbal
```

### 3. Configure Environment

```bash
cp .env.example .env
```

Edit `.env` with your settings:

```env
APP_NAME=Libiverse
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=libiverse
DB_USERNAME=root
DB_PASSWORD=

# Required for book search & import
GOOGLE_BOOKS_API_KEY=your_google_books_api_key_here

# Optional but recommended (video thumbnails)
APYHUB_API_KEY=your_apyhub_key_here

# Optional – for email notifications & password reset
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_user
MAIL_PASSWORD=your_mailtrap_pass
```

Generate app key:

```bash
php artisan key:generate
```

### 4. Run Migrations & Seed Initial Data

```bash
php artisan migrate
php artisan db:seed
```

This creates all tables and adds sample forums, events, challenges, and badges.

### 5. Link Storage for Media Uploads

```bash
php artisan storage:link
```

Uploaded files will be accessible at `http://localhost:8000/storage/...`


### 7. Start the Server

```bash
php artisan serve
```

API base URL:  
**http://127.0.0.1:8000/api/v1/**

### 8. Test Login

```bash
POST http://127.0.0.1:8000/api/v1/auth/login
{
  "email": "admin@libiverse.com",
  "password": "password"
}
```

Use the returned `token` in the header:

```
Authorization: Bearer your_jwt_token_here
```

You're ready to explore the API!

## Useful Artisan Commands

```bash
php artisan make:module NewModule        # Create a new module
php artisan migrate:fresh --seed          # Reset DB with fresh data
php artisan storage:link                  # Re-link storage
php artisan cache:clear && php artisan config:clear && php artisan route:clear
php artisan test                          # Run tests
```

## Main API Endpoints (v1)

| Feature               | Example Endpoint                              |
|-----------------------|-----------------------------------------------|
| Auth                  | `POST /api/v1/auth/register`, `/login`        |
| Profile               | `GET /api/v1/profile`, `PUT /api/v1/profile`  |
| Users                 | `GET /api/v1/users/{uuid}`, `/follow`         |
| Forums                | `GET /api/v1/forums`, `/forums/{id}/threads`  |
| Threads & Posts       | `POST /api/v1/threads/{thread}/posts`         |
| Books                 | `GET /api/v1/books/search/google`             |
| Events                | `GET /api/v1/events`, `POST /events/{id}/rsvp`|
| Challenges            | `GET /api/v1/challenges`, `/join`             |
| Notifications         | `GET /api/v1/notifications`                   |
| Admin                 | `GET /api/v1/admin/reported-posts`            |

## Project Structure

```
Modules/
├── Auth/         → Registration, login, approval workflow
├── Forum/        → Forums, threads, membership, activity feed
├── Post/         → Posts, media, likes, saves, reports
├── Book/         → Book CRUD + Google Books integration
├── Challenge/    → Reading challenges, progress, badges
├── Event/        → Events & RSVPs
├── User/         → Profiles, follows, stats, disable
├── Notification/ → All notification logic
```

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

MIT License – feel free to use and modify.

---

**Made with love for the British Council Library community in Myanmar**

Happy coding!
