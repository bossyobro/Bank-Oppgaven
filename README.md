# Bank System Project

A secure online banking system developed as a school project. The system features two-factor authentication, transaction logging, and interest calculations.

## Features

- Secure user authentication with 2FA
- Multiple account types (personal, business)
- Transaction history and logging
- Interest rate management
- Admin dashboard
- Account management

## Technical Details

- PHP 8.0+
- MySQL
- Google Authenticator for 2FA
- PDO for database connections
- Responsive design

## Setup

1. Import `static/bank.sql` to create the database
2. Configure database connection in `db.php`
3. Ensure write permissions for logs

## Security Features

- Password hashing
- Two-factor authentication
- Session management
- Activity logging
- Input validation
- Prepared statements

## Known Issues

- Interest calculations might need adjustment for leap years
- Some UI elements need mobile optimization
- KID validation could be more robust

## Project Structure

- `index.php` - Main dashboard
- `accounts.php` - Account management
- `transactions.php` - Transaction history
- `profile.php` - User profile
- `admin.php` - Admin dashboard
- `register.php` - User registration
- `login.php` - User login
- `logout.php` - User logout
- `setup_2fa.php` - 2FA setup
- `verify_2fa.php` - 2FA verification

## Database Structure

- `users` table
- `accounts` table
- `transactions` table
- `admin` table

## Admin Credentials

- Username: `Rab`
- Password: `admin123`



