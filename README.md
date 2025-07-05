# VISA Dossier Upload Feature

A simplified VISA Dossier management system using Laravel (API-only) for the backend and React Router for the frontend.

## Documentation

## Project Structure

- `backend/`: Laravel API for file management
- `frontend/`: React application for the user interface

## Features

- Upload files (PDF, PNG, JPG) up to 4MB
- List uploaded files grouped by type (Passport, Utility Bill, Other)
- Delete files
- Preview uploaded files
- Responsive UI with visual feedback

## Backend Setup

### Prerequisites

- PHP 8.1 or higher
- Composer
- SQLite (for development)

### Installation

1. Navigate to the backend directory:

```bash
cd backend
```

2. Install dependencies:

```bash
composer install
```

3. Create environment file:

```bash
cp .env.example .env
```

4. Generate application key:

```bash
php artisan key:generate
```

5. Create SQLite database:

```bash
touch database/database.sqlite
```

6. Run migrations:

```bash
php artisan migrate
```

7. Create storage link:

```bash
php artisan storage:link
```

### Running the Backend

Start the Laravel development server:

```bash
php artisan serve
```

The API will be available at http://localhost:8000.

### API Endpoints

- `GET /api/dossier-files`: List all files grouped by type
- `POST /api/dossier-files`: Upload a new file
- `DELETE /api/dossier-files/{id}`: Delete a file
