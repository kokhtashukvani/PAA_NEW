# Purchasing Assistant SPA

This is a modern Single-Page Application (SPA) for managing purchase requests, built with a plain PHP REST API backend and a Vue.js 3 frontend.

## Technology Stack

*   **Backend:** Plain PHP 8+ (No Frameworks)
*   **Frontend:** Vue.js 3 (with Vite, Vue Router, and Pinia)
*   **Database:** SQLite
*   **Styling:** Tabler CSS Framework
*   **PHP Dependencies:** `firebase/php-jwt` for authentication.
*   **Frontend Dependencies:** `axios`, `vuedraggable`, etc.

## Prerequisites

Before you begin, ensure you have the following installed:
*   PHP (version 8.0 or higher)
*   Node.js and npm (Node Package Manager)
*   Composer (for PHP dependency management)

## Installation

This project includes a comprehensive installer script to automate the setup process. To install the application, simply run the following command from the root of the project directory:

```bash
php install.php
```

This script will perform the following actions:
1.  **Create Database:** It will create and configure the `purchasing.sqlite` database file with the required tables.
2.  **Create Admin User:** It will create a default administrator account so you can access the admin-only features. The credentials are:
    *   **Username:** `admin`
    *   **Password:** `password123`
3.  **Seed Default Form:** It will create a sample "Purchase Request Form" so that the application is immediately usable.
4.  **Install Frontend Dependencies:** It will run `npm install` in the `/frontend` directory to download all necessary JavaScript packages.

## Running the Application

To run the application, you need to start two separate development servers: one for the PHP backend and one for the Vue.js frontend.

### 1. Backend Server

Navigate to the `/api` directory and use PHP's built-in web server. It's recommended to run it on a port other than the frontend's default, for example, port 8000.

```bash
cd api
php -S localhost:8000
```
The backend API will now be running at `http://localhost:8000`.

### 2. Frontend Server

In a separate terminal, navigate to the `/frontend` directory and run the Vite development server.

```bash
cd frontend
npm run dev
```
The Vite server will typically start on port 5173. It will print the exact URL in the console (e.g., `http://localhost:5173`).

You can now open the frontend URL in your browser to use the application. The frontend is configured to send API requests to the PHP server.
