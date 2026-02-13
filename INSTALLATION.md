# RBAC AutoBase - Installation Guide

## Prerequisites
- PHP 8.2 or higher
- Composer
- Node.js & NPM
- MySQL/PostgreSQL/SQLite

## Installation Steps

### 1. Install PHP Dependencies
```bash
composer install
```

### 2. Install Node Dependencies
```bash
npm install
```

### 3. Environment Configuration
Copy `.env.example` to `.env` and configure your database:
```bash
copy .env.example .env
```

Edit `.env` file:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 4. Generate Application Key
```bash
php artisan key:generate
```

### 5. Run Migrations
```bash
php artisan migrate
```

### 6. Seed Initial Data
```bash
php artisan db:seed --class=RBACSeeder
```

This will create:
- Admin user (email: admin@example.com, password: password)
- Admin role with all permissions
- Default permissions for Users, Roles, Permissions, and Menus
- Default menu structure

### 7. Build Frontend Assets
```bash
npm run build
```

For development:
```bash
npm run dev
```

### 8. Start Development Server
```bash
php artisan serve
```

Visit: http://localhost:8000

## Default Login Credentials
- Email: admin@example.com
- Password: password

**IMPORTANT:** Change the default password after first login!

## Features

### RBAC System
- User Management (CRUD)
- Role Management (CRUD)
- Permission Management (CRUD)
- Menu Management (CRUD)
- Role-Permission Assignment
- Role-Menu Assignment
- User-Role Assignment

### UI/UX
- Bootstrap 5.3.2
- Responsive Design
- Modern AutoBase Layout
- Top Navigation Menu
- Breadcrumb Navigation
- Dynamic Menu based on User Roles

### Security
- Authentication
- Authorization (Role & Permission based)
- Middleware for Permission & Role checking
- Password Hashing

## Usage Examples

### Protecting Routes with Permissions
```php
Route::get('/users', [UserController::class, 'index'])
    ->middleware('permission:users.view');
```

### Protecting Routes with Roles
```php
Route::get('/admin', [AdminController::class, 'index'])
    ->middleware('role:ADMIN');
```

### Checking Permissions in Blade
```blade
@if(auth()->user()->hasPermission('users.create'))
    <a href="{{ route('users.create') }}">Create User</a>
@endif
```

### Checking Roles in Blade
```blade
@if(auth()->user()->hasRole('ADMIN'))
    <p>You are an administrator</p>
@endif
```

## Database Schema

### Tables Created
- ms_users
- ms_role
- ms_permissions
- ms_role_permissions
- ms_user_roles
- ms_menus
- ms_role_menus

## Troubleshooting

### Migration Errors
If you encounter foreign key errors, ensure migrations run in this order:
1. create_users_table
2. create_rbac_tables
3. create_ms_menus_table

### Permission Denied
Make sure storage and bootstrap/cache directories are writable:
```bash
chmod -R 775 storage bootstrap/cache
```

## Support
For issues or questions, please check the documentation or contact support.
