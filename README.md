# E-Commerce API System

**Repository**: [https://github.com/TukaHeba/Backend-Test](https://github.com/TukaHeba/Backend-Test)

## Description

This project is an **E-Commerce API System** built with **Laravel 12** that provides a comprehensive RESTful API for managing products, categories, shopping carts, and orders. It implements role-based access control where administrators can perform full CRUD operations on products and categories, while customers can browse products, manage their shopping carts, and place orders. The system employs **service layer architecture**, **observers**, **policies**, and **queue jobs** for sending email notifications when orders are created.

### Key Features:

- **Authentication**:
  - User registration and login
  - Token-based authentication using Laravel Sanctum
  - Role-based access control (admin/customer)

- **Product Management**:
  - **Admin Operations**: Create, read, update, delete, and restore soft-deleted products
  - **Customer Operations**: View available products with filtering by category
  - Product quantity management with automatic updates on order creation/cancellation

- **Category Management**:
  - **Admin Operations**: Full CRUD operations on categories
  - **Customer Operations**: View categories and products by category

- **Shopping Cart**:
  - Add products to cart
  - View cart with automatic total calculation
  - Remove items from cart
  - Clear entire cart

- **Order Management**:
  - Create orders from cart items
  - View personal orders (customers can only see their own orders)
  - Cancel pending orders with automatic product quantity restoration
  - Automatic order confirmation email notifications

- **Email Notifications**:
  - **Order Confirmation Emails**: Customers receive email notifications when they place an order via queue jobs

- **Console Commands**:
  - **Cleanup Command**: Delete cancelled orders older than 30 days

- **Architecture Patterns**:
  - Service layer for business logic separation
  - Observers for model events (Category, Order, Product)
  - Policies for authorization (Order, Product)
  - Request validation classes for input validation

### Technologies Used:

- **Laravel 12**
- **PHP 8.3**
- **SQLite** (for testing) / **MySQL** (for production)
- **Laravel Sanctum** (API authentication)
- **Laravel Pint** (code formatting)
- **PHPUnit** (testing framework)
- **Composer**


---

## Installation

### Prerequisites

- PHP 8.3 or higher
- Composer

- MySQL (for production) or SQLite (for development/testing)

### Steps to Run the Project:

1. **Clone the repository**:

   ```bash
   git clone https://github.com/TukaHeba/Backend-Test.git
   cd Backend-Test
   ```

2. **Install Dependencies**:

   ```bash
   composer install
   ```

3. **Create Environment File**:

   ```bash
   cp .env.example .env
   ```

   Update the `.env` file with your database configuration:
   - `DB_CONNECTION=mysql`
   - `DB_HOST=127.0.0.1`
   - `DB_PORT=3306`
   - `DB_DATABASE=your_database_name`
   - `DB_USERNAME=your_username`
   - `DB_PASSWORD=your_password`

4. **Generate Application Key**:

   ```bash
   php artisan key:generate
   ```

5. **Run Migrations**:

   ```bash
   php artisan migrate
   ```

6. **Seed the Database**:

   ```bash
   php artisan db:seed
   ```

   This will create sample users (admin and customer), products, categories, and orders.

7. **Run the Application**:

   ```bash
   php artisan serve
   ```

---

## Testing

The project includes comprehensive test coverage with both Feature and Unit tests.

### Running Tests:

1. **Run all tests**:

   ```bash
   php artisan test
   ```

   Or using Composer:

   ```bash
   composer test
   ```

2. **Run specific test suites**:

   ```bash
   # Run only Feature tests
   php artisan test --testsuite=Feature

   # Run only Unit tests
   php artisan test --testsuite=Unit
   ```

3. **Run a specific test file**:

   ```bash
   php artisan test tests/Feature/OrderTest.php
   ```

### Test Coverage:

- **Feature Tests**:
  - `AuthTest.php` - User registration and login
  - `CartTest.php` - Shopping cart operations
  - `OrderTest.php` - Order creation, viewing, and cancellation
  - `ProductTest.php` - Product CRUD operations and filtering

- **Unit Tests**:
  - `CalculationTest.php` - Business logic calculations
  - `ProductAccessorTest.php` - Product model accessors
  - `ProductScopeTest.php` - Product model scopes

---

## Jobs and Queues

The system uses Laravel's queue system to handle asynchronous tasks, particularly for sending email notifications.

### Job Class:

- **SendOrderConfirmationEmail**: Dispatched when an order is created to send confirmation emails to customers asynchronously.

### Steps to Run Jobs and Send Emails:

1. **Configure Queue Connection**:

   Ensure your `.env` file has the queue connection set (default is `database`):

   ```env
   QUEUE_CONNECTION=database
   ```

2. **Start the Queue Worker**:

   ```bash
   php artisan queue:work
   ```

   This will process jobs from the queue. When an order is created, the `SendOrderConfirmationEmail` job will be dispatched and processed by the queue worker.


---

## Console Commands

The project includes a custom console command for maintenance tasks.

### Available Commands:

1. **Cleanup Cancelled Orders**:

   ```bash
   php artisan orders:cleanup-cancelled
   ```

   This command deletes cancelled orders that are older than 30 days. It includes a confirmation prompt by default.

   To run without confirmation:

   ```bash
   php artisan orders:cleanup-cancelled --force
   ```

---

## Code Formatting

The project uses **Laravel Pint** for code formatting to maintain consistent code style across the codebase.

### Formatting Commands:

1. **Format all files**:

   ```bash
   composer format
   ```

   Or directly:

   ```bash
   ./vendor/bin/pint
   ```

2. **Check formatting without making changes**:

   ```bash
   composer format:test
   ```

   Or directly:

   ```bash
   ./vendor/bin/pint --test
   ```

### Formatting Rules:

- Follows PSR-12 coding standards
- 4 spaces for indentation
- UTF-8 encoding
- LF line endings

---

## API Documentation

The complete API documentation with all available endpoints, request/response examples, and authentication details can be found at:

**[Postman API Collection](https://documenter.getpostman.com/view/34424205/2sB3WwpH3j)**

### API Endpoints Overview:

- **Authentication**: `/api/v1/register`, `/api/v1/login`, `/api/v1/logout`
- **Products**: `/api/v1/products` (with filtering and admin CRUD)
- **Categories**: `/api/v1/categories` (with admin CRUD)
- **Cart**: `/api/v1/cart` (authenticated users only)
- **Orders**: `/api/v1/orders` (authenticated users only)

All API endpoints are prefixed with `/api/v1` and use JSON responses with a standardized format:

```json
{
  "success": true,
  "data": {},
  "message": "Success message"
}
```

---

## Project Structure

### Key Directories:

- `app/Http/Controllers/v1/` - API controllers
- `app/Http/Requests/` - Form request validation classes
- `app/Http/Resources/` - API resource transformers
- `app/Models/` - Eloquent models
- `app/Services/` - Business logic service classes
- `app/Jobs/` - Queue job classes
- `app/Notifications/` - Notification classes
- `app/Observers/` - Model observers
- `app/Policies/` - Authorization policies
- `app/Console/Commands/` - Artisan commands
- `tests/Feature/` - Feature tests
- `tests/Unit/` - Unit tests
