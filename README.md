# News Package

Shared models, migrations, and services for multi-tenant news platform.

## Installation

```bash
composer require xtramile/news-internal
```

## Requirements

- PHP 8.2 or higher
- Laravel 11.0 or higher

## Usage

The package will automatically register the service provider. After installation, run migrations:

```bash
php artisan migrate
```

## Available Services

- `PostQueryService` - Query posts with various filters
- `ViewCounter` - Track and record post views

## Models

- `Post` - News post model
- `Category` - Post category model
- `Tag` - Post tag model
- `Tenant` - Multi-tenant model
- `PostView` - Post view tracking model

## License

MIT License

