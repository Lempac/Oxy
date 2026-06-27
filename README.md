# Oxy
## About
This is a project for learning the development of social team collaboration tool,
that lets you create, manage groups with roles in company or group of people with goal. 
![UI](/assets/ui.png)

### The groups have the ability to chat or voice call.
![Group](/assets/group.png)
![Group Chat](/assets/group_chat.png)
![Group Voice Chat](/assets/group_voice_chat.png)

### And collaborate on whiteboard.

### Settings for managing groups and whiteboards.
![Settings](/assets/group_settings.png)

## Technologies Used

This project is built using the modern **VILT** stack alongside powerful real-time collaboration libraries:

### Core Stack
* **Laravel 11:** The robust PHP framework powering the backend, authentication, and background processing.
* **Vue 3:** A progressive JavaScript framework used for building the reactive user interfaces.
* **Inertia.js:** Glues Laravel and Vue together, allowing the creation of a modern single-page app without building complex APIs.
* **Tailwind CSS & DaisyUI:** Utility-first CSS framework and component library for rapid and beautiful UI development.

### Real-Time & Collaboration
* **Laravel Reverb & Echo:** First-party WebSocket server and client for real-time broadcasting (used for live chat, voice calls, and notifications).
* **Yjs & @y/websocket-server:** A CRDT framework and WebSocket server used to handle conflict-free, real-time synchronization for the collaborative whiteboard.
* **Konva (vue-konva):** 2D HTML5 Canvas framework used to draw and interact with elements on the whiteboard.

### Infrastructure
* **Docker & FrankenPHP:** Containerized setup for easy deployments, utilizing the high-performance FrankenPHP server and Supervisor to manage workers.

## Getting Started

### Using Nix & devenv (Recommended for Nix users)
This project comes with a built-in Nix developer environment powered by [devenv.sh](https://devenv.sh). It automatically provides the correct versions of PHP, Node.js, pnpm, and SQLite, and allows you to run all services at once.

1. Ensure you have Nix and `devenv` installed.
2. Enter the development shell (or rely on `direnv`):
   ```bash
   devenv shell
   ```
3. Install dependencies and set up the project:
   ```bash
   composer install
   pnpm install
   cp .env.example .env
   php artisan key:generate
   php artisan migrate
   php artisan storage:link
   ```
4. Start all required background processes in one go:
   ```bash
   devenv up
   ```

### Using Docker (Recommended)
The easiest way to get the application running is by using Docker Compose, which seamlessly sets up the web server, WebSockets, background queues, and the Yjs collaboration server.

1. Copy the example environment file:
   ```bash
   cp .env.example .env
   ```
2. Build and start the containers:
   ```bash
   docker compose up --build
   ```
*(Note: If you are relying on SQLite, ensure `DB_CONNECTION=sqlite` is set in your `.env` and the SQLite file exists, or configure your database credentials accordingly).*

### Manual Local Setup
If you prefer running the application without Docker, ensure you have PHP, Composer, Node.js, and a database server (like MySQL/MariaDB or SQLite) installed.

1. Install backend and frontend dependencies:
   ```bash
   composer install
   npm install
   ```
2. Set up the environment file and generate the application key:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
3. Run the database migrations and create the storage symlink:
   ```bash
   php artisan migrate
   php artisan storage:link
   ```

## Running the Application (Local)

If you are running the project manually locally, you will need to start the following services to ensure all features (including the real-time chat, queues, and whiteboard) function correctly:

```bash
php artisan serve
npm run dev
php artisan reverb:start
php artisan queue:work
npm run yjs
```

### Note on Email Verification
If you don't have an email server configured for local development and are having trouble registering, go to `app/Models/User.php` and remove this interface implementation:
```php
use Illuminate\Contracts\Auth\MustVerifyEmail;
```