# Laravel Reverb Absensi

Aplikasi absensi real-time menggunakan Laravel 12, Inertia.js, Vue 3, dan Laravel Reverb (WebSocket).

## Requirements

- PHP 8.2 atau lebih tinggi
- Composer
- Node.js & NPM
- SQLite (default) atau MySQL/PostgreSQL

## Tech Stack

- **Backend**: Laravel 12
- **Frontend**: Vue 3 + TypeScript
- **UI Framework**: Inertia.js
- **Authentication**: Laravel Fortify
- **Real-time**: Laravel Reverb (WebSocket)
- **Routing**: Laravel Wayfinder
- **Styling**: Tailwind CSS

## Installation

1. Clone repository ini:
```bash
git clone <repository-url>
cd laravel-reverb-absensi
```

2. Install dependencies:
```bash
composer install
npm install
```

3. Setup environment:
```bash
copy .env.example .env
php artisan key:generate
```

4. Setup database:
```bash
php artisan migrate
```

5. Link storage:
```bash
php artisan storage:link
```

## Development

Jalankan development server dengan 3 terminal terpisah:

**Terminal 1 - Laravel Server:**
```bash
php artisan serve
```

**Terminal 2 - Vite Dev Server:**
```bash
npm run dev
```

**Terminal 3 - Laravel Reverb (WebSocket):**
```bash
php artisan reverb:start
```

Atau gunakan script composer untuk menjalankan semuanya sekaligus:
```bash
composer dev
```

Aplikasi akan berjalan di `http://localhost:8000`

## Build untuk Production

```bash
npm run build
php artisan optimize
```

## Testing

```bash
composer test
```

atau

```bash
php artisan test
```

## Code Quality

Format code dengan Laravel Pint:
```bash
./vendor/bin/pint
```

## License

MIT License
