# E-Prodajalna Project

An e-commerce web application built with PHP, MySQL, and JavaScript.

## Project Structure

```
├── assets/           # Static assets (images, CSS, etc.)
├── config/          # Configuration files
├── database/        # Database scripts and migrations
├── includes/        # PHP includes and utilities
├── js/             # JavaScript files
├── modules/        # Application modules
│   ├── admin/      # Admin panel
│   ├── api/        # API endpoints
│   ├── prodajalec/ # Seller interface
│   └── stranka/    # Customer interface
└── public/         # Public facing PHP files
```

## Setup Instructions

1. Import the database schema:
   ```sql
   mysql -u your_username -p your_database < database/database.sql
   ```

2. Configure your database connection:
   - Copy `config/config.example.php` to `config/config.php`
   - Update the database credentials in `config.php`

3. Start your local server:
   - Make sure XAMPP/Apache is running
   - Access the application at `http://localhost/Seminarska_vaje/`

## Features

- User Authentication (Admin, Seller, Customer)
- Product Management
- Shopping Cart
- Order Management
- User Profile Management

## Contributors

[Your Team Information Here]
