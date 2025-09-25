# CSV Contact Importer

A Laravel and Vue.js application for importing contacts from CSV files with deduplication and validation.

## Features

- CSV file upload and processing
- Contact deduplication by email
- Data validation
- Asynchronous processing for large files
- Import summary with statistics
- Gravatar integration
- Paginated contact listing
- Automated tests

## Requirements

- PHP 8.2+
- Composer
- Node.js & NPM
- Laravel 10.x
- SQLite/MySQL/PostgreSQL

## Installation

1. Clone the repository:
```bash
git clone <repository-url>
cd <project-folder>
```

2. Install PHP dependencies:
```bash
composer install
```

3. Install Node.js dependencies:
```bash
npm install --legacy-peer-deps
```

4. Copy environment file and generate key:
```bash
cp .env.example .env
php artisan key:generate
```

5. Configure your database in `.env`

6. Run migrations:
```bash
php artisan migrate
```

7. Start the development server:
```bash
composer run dev
``` 
Or

7. Start the development server:
```bash
php artisan serve
```

8. In a separate terminal, compile assets:
```bash
npm run dev
```

9. Run queue work
```bash
php artisan queue:work
```

## Usage

1. Access the application at `http://localhost:8000`
2. Register/Login to access the contacts page
3. Use the upload form to import CSV files
4. View import summary and contact list

## CSV Format

The CSV file should contain the following columns (order may vary):
- name
- email (unique)
- phone
- birthdate (YYYY-MM-DD format)

Example:
```csv
name,email,phone,birthdate
John Doe,john@example.com,1234567890,1990-01-01
```

## Testing

Run the test suite:
```bash
php artisan test
```

## Technical Decisions

- Used Laravel Breeze for authentication scaffolding
- Implemented asynchronous processing using Laravel's queue system
- Used Gravatar for profile pictures with identicon fallback
- Implemented real-time import statistics using cache
- Added comprehensive validation for data integrity
