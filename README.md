# Laravel Workflow System

This project implements a flexible workflow management system using Laravel, following a Service-Repository pattern, designed for handling a series of dependent tasks.

## Features

* **Workflow Orchestration:** Define and execute complex workflows with dependent tasks.
* **Asynchronous Task Processing:** Utilizes Laravel Queues for background task execution (image processing, notifications, etc.).
* **Service-Repository Pattern:** Clean separation of concerns for business logic and data persistence.
* **Event-Driven Communication:** Tasks communicate status changes via Laravel Events and Listeners.
* **Dockerized Environment:** Easily run the entire application stack using Docker Compose.

## Technologies Used

* **Laravel:** PHP Framework
* **MySQL:** Database
* **Redis:** Queue Driver & Caching
* **Nginx:** Web Server
* **Docker & Docker Compose:** Containerization

## Getting Started

Follow these steps to get the project up and running on your local machine.

### Prerequisites

* **Docker Desktop:** Make sure Docker Desktop (or Docker Engine for Linux) is installed and running.
    * [Download Docker Desktop](https://www.docker.com/products/docker-desktop)
* **Git:** For cloning the repository.

### Installation

1.  **Clone the repository:**
    ```bash
    git clone <your-repository-url>
    cd workflow-app # Or whatever your project folder is named
    ```

2.  **Create `.env` file:**
    Copy the example environment file and generate an application key:
    ```bash
    cp .env.example .env
    ```
    Open the newly created `.env` file and make the following adjustments:

    * Set your database credentials (e.g., `DB_USERNAME=root`, `DB_PASSWORD=password`).
    * Ensure **`DB_HOST=mysql`** (this matches the service name in `docker-compose.yml`).
    * Ensure **`REDIS_HOST=redis`** (this matches the service name in `docker-compose.yml`).
    * Set **`QUEUE_CONNECTION=redis`** or `QUEUE_CONNECTION=database` (Redis is recommended for production).

    Then, generate the application key:
    ```bash
    php artisan key:generate
    ```

3.  **Build and Run Docker Containers:**
    From the project root, run:
    ```bash
    docker-compose up -d --build
    ```
    This command will:
    * Build the custom PHP-FPM image (if not already built).
    * Create and start Nginx, PHP-FPM, MySQL, and Redis containers in detached mode (`-d`).

4.  **Install Composer Dependencies (inside the PHP container):**
    ```bash
    docker-compose exec app composer install
    ```

5.  **Run Database Migrations:**
    ```bash
    docker-compose exec app php artisan migrate
    ```

6.  **Start the Queue Worker (important for task processing):**
    You need a separate terminal or Docker Compose service for this. For local development, running it in your terminal is fine:
    ```bash
    docker-compose exec app php artisan queue:work --tries=3 --timeout=60
    ```
    * **Note:** For production, consider using a process manager like Supervisor or Laravel Horizon to ensure your queue worker is always running.

## Usage

Once all containers are up and running, and the queue worker is active, you can interact with the API.

The application will be accessible at `http://localhost`.

### Example API Endpoints:

* **Start an Example Workflow (POST):**
    `http://localhost/api/workflows/start-example`
    ```bash
    curl -X POST http://localhost/api/workflows/start-example
    ```
    This will return a `workflow_id`.

* **Get Workflow Status (GET):**
    `http://localhost/api/workflows/{workflow_id}/status`
    (Replace `{workflow_id}` with the ID you received from the start endpoint)
    ```bash
    curl http://localhost/api/workflows/1/status
    ```

Check your Laravel logs (`storage/logs/laravel.log`) inside the `app` container to see the workflow and task execution progress.
To view logs:
```bash
docker-compose exec app tail -f storage/logs/laravel.log