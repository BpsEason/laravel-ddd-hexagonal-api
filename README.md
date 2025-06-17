# Laravel Enterprise-Grade E-commerce Backend API

![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![DDD](https://img.shields.io/badge/Architecture-DDD-blue?style=for-the-badge)
![Hexagonal](https://img.shields.io/badge/Architecture-Hexagonal-green?style=for-the-badge)
![Docker](https://img.shields.io/badge/Containerization-Docker-blue?style=for-the-badge&logo=docker&logoColor=white)
![Terraform](https://img.shields.io/badge/IaC-Terraform-7B42BC?style=for-the-badge&logo=terraform&logoColor=white)
![CI/CD](https://img.shields.io/badge/DevOps-CI%2FCD-orange?style=for-the-badge)

This project provides a robust and scalable **e-commerce backend API** built with the **Laravel framework**, demonstrating an architecture designed for **enterprise-level applications**. It emphasizes maintainability, testability, and extensibility through modern software engineering principles and DevOps practices.

## Table of Contents

1. [Project Overview](#1-project-overview)
2. [Key Architectural Principles](#2-key-architectural-principles)
3. [Core Features Implemented](#3-core-features-implemented)
4. [Technology Stack](#4-technology-stack)
5. [Getting Started](#5-getting-started)
   - [Prerequisites](#prerequisites)
   - [Local Development Setup (Docker Compose)](#local-development-setup-docker-compose)
   - [Running Migrations and Seeders](#running-migrations-and-seeders)
6. [Project Structure](#6-project-structure)
7. [API Endpoints (Example)](#7-api-endpoints-example)
8. [Deployment & Infrastructure as Code (IaC)](#8-deployment--infrastructure-as-code-iac)
9. [Monitoring & Observability](#9-monitoring--observability)
10. [Testing](#10-testing)
11. [Contributing](#11-contributing)
12. [License](#12-license)

---

## 1. Project Overview

This repository showcases a structured approach to building a Laravel backend for an e-commerce platform. The primary goal is to illustrate how to manage complexity in growing applications by strictly separating concerns, adopting domain-centric thinking, and leveraging automation for development and deployment.

It serves as a comprehensive example for developers and teams looking to implement:
- A clean, modular, and maintainable Laravel codebase.
- Advanced architectural patterns for complex business domains.
- Automated infrastructure and deployment workflows.

## 2. Key Architectural Principles

The project's design is heavily influenced by the following principles:

- **Domain-Driven Design (DDD)**: Focuses on modeling the complex business domain of e-commerce (e.g., Orders, Products, Inventory) into rich, behavior-driven domain entities and services. The domain layer is the heart of the application, independent of any framework or persistence technology.
- **Hexagonal Architecture (Ports and Adapters)**: Ensures that the core business logic remains isolated from external concerns like databases, UI, or third-party APIs. Interactions happen through clearly defined "ports" (interfaces) and "adapters" (implementations), enhancing testability and flexibility in changing technologies.
- **Strict Separation of Concerns (Layered Architecture)**:
  - **Domain Layer (`app/Domain`)**: Contains the core business rules, entities, value objects, aggregates, and domain services. It is framework and technology agnostic.
  - **Application Layer (`app/Application`)**: Defines the application's use cases. It orchestrates domain objects, handles application-specific flows, and manages transactions.
  - **Infrastructure Layer (`app/Infrastructure`)**: Provides concrete technical implementations for interfaces defined in the Domain layer (e.g., Eloquent Repositories, external service integrations like payment gateways, email services).
  - **Interface Layer (`app/Http`)**: Handles HTTP requests and responses. Controllers are kept "thin," primarily delegating tasks to the Application layer and formatting responses.
- **Infrastructure as Code (IaC)**: All infrastructure components (servers, databases, networking, load balancers, etc.) are defined and managed as code using specialized tools. This ensures environment consistency, automates provisioning, and enables rapid disaster recovery.
- **Containerization (Docker)**: Applications and their dependencies are packaged into portable, isolated containers, ensuring consistent environments across development, testing, and production.
- **Continuous Integration / Continuous Deployment (CI/CD)**: Automates the entire software delivery pipeline, from code commit to production deployment, ensuring fast, frequent, and reliable releases.
- **Observability**: Emphasizes comprehensive logging, monitoring, and error tracking to gain deep insights into system health and performance.

## 3. Core Features Implemented

The current example focuses on a critical e-commerce workflow:

- **Order Placement**: Demonstrates the end-to-end process of creating an order from a cart, including:
  - Product stock validation and decrement.
  - Order and Order Item creation.
  - Persistence of order and product changes.
  - Dispatching a `OrderPlaced` domain event for subsequent asynchronous processes (e.g., sending order confirmation emails).

## 4. Technology Stack

- **Backend Framework**: Laravel (PHP)
- **Database**: MySQL / PostgreSQL (via Docker Compose for local, RDS for production)
- **Caching & Queues**: Redis (via Docker Compose for local, ElastiCache for production)
- **Containerization**: Docker, Docker Compose
- **Infrastructure as Code**: Terraform (for cloud resource provisioning)
- **Configuration Management (Optional/For EC2)**: Ansible (for server setup and application deployment)
- **CI/CD**: GitLab CI/CD (for automated testing, building, and deployment)
- **Monitoring & Logging**: (Placeholder, to be integrated with tools like Prometheus/Grafana, ELK Stack, Sentry, CloudWatch)

## 5. Getting Started

Follow these steps to set up and run the project locally.

### Prerequisites

- PHP >= 8.2
- Composer
- Docker & Docker Compose (recommended for local development)
- Git

### Local Development Setup (Docker Compose)

1. **Clone the repository:**
   ```bash
   git clone https://github.com/your-username/laravel-ecommerce-api-ddd.git
   cd laravel-ecommerce-api-ddd
   ```

2. **Copy environment file:**
   ```bash
   cp .env.example .env
   ```
   Adjust database and Redis credentials in `.env` if necessary, though Docker Compose will set up defaults.

3. **Build and start Docker containers:**
   ```bash
   docker-compose up -d --build
   ```
   This will build your Laravel application container, Nginx, MySQL, and Redis services.

4. **Install Composer dependencies:**
   ```bash
   docker-compose exec app composer install
   ```

5. **Generate application key:**
   ```bash
   docker-compose exec app php artisan key:generate
   ```

### Running Migrations and Seeders

1. **Run database migrations:**
   ```bash
   docker-compose exec app php artisan migrate
   ```

2. **Seed initial data (optional, but recommended for testing):**
   Ensure you have a `ProductSeeder.php` and call it from `database/seeders/DatabaseSeeder.php`.

   ```php
   // database/seeders/ProductSeeder.php
   <?php

   namespace Database\Seeders;

   use Illuminate\Database\Seeder;
   use Illuminate\Support\Facades\DB;
   use Illuminate\Support\Str;

   class ProductSeeder extends Seeder
   {
       public function run(): void
       {
           DB::table('products')->insert([
               [
                   'id' => (string) Str::uuid(),
                   'name' => 'Sample Product A',
                   'price' => 19.99,
                   'stock' => 100,
                   'created_at' => now(),
                   'updated_at' => now(),
               ],
               [
                   'id' => (string) Str::uuid(),
                   'name' => 'Sample Product B',
                   'price' => 29.50,
                   'stock' => 50,
                   'created_at' => now(),
                   'updated_at' => now(),
               ],
           ]);
       }
   }
   ```

   ```php
   // database/seeders/DatabaseSeeder.php
   <?php

   namespace Database\Seeders;

   use Illuminate\Database\Seeder;

   class DatabaseSeeder extends Seeder
   {
       public function run(): void
       {
           $this->call([
               ProductSeeder::class,
               // ... other seeders
           ]);
       }
   }
   ```

   Run the seeder:
   ```bash
   docker-compose exec app php artisan db:seed
   ```

Your application should now be running and accessible at `http://localhost`.

## 6. Project Structure

The project adheres to a layered architecture influenced by DDD and Hexagonal principles:

```
.
├── app/
│   ├── Console/             # Artisan commands
│   ├── Exceptions/          # Custom exceptions
│   ├── Http/
│   │   ├── Controllers/     # HTTP request handlers (thin, delegates to Application layer)
│   │   ├── Requests/        # Form requests for validation
│   │   └── Resources/       # API Resources for response formatting
│   ├── Providers/           # Service Providers for binding interfaces, event registration, etc.
│   ├── Domain/              # Core Business Logic (Framework and Persistence Agnostic)
│   │   ├── Entities/        # Domain entities with behavior (e.g., Order, Product)
│   │   ├── Aggregates/      # Aggregate roots for consistency boundaries
│   │   ├── ValueObjects/    # Immutable value objects (e.g., Price, Address)
│   │   ├── Services/        # Domain services for cross-entity operations (e.g., OrderPlacementService)
│   │   ├── Repositories/     # Domain Repository Interfaces (Contracts)
│   │   └── Events/          # Domain Events (e.g., OrderPlaced)
│   ├── Application/         # Application Use Cases/Flows
│   │   ├── Commands/        # Command objects (input DTOs)
│   │   ├── Queries/         # Query objects (input DTOs for read models)
│   │   ├── Handlers/        # Command Handlers / Query Handlers (Application Services / Use Cases)
│   │   ├── DTOs/            # Data Transfer Objects
│   │   └── Jobs/            # Application-level Jobs (queueable operations)
│   ├── Infrastructure/      # Technical Details & External Integrations
│   │   ├── Persistence/     # Data persistence implementations
│   │   │   ├── Eloquent/    # Eloquent Repository implementations
│   │   │   └── Models/      # Eloquent ORM Models (pure database mapping)
│   │   ├── Services/        # External service integrations (e.g., PaymentGateway)
│   │   ├── Events/          # Infrastructure-level event listeners (e.g., SendOrderConfirmationEmail)
│   │   └── Security/        # Auth/Auth implementations
│   └── Support/             # Generic utility classes
├── bootstrap/
├── config/
├── database/
│   ├── migrations/          # Database migrations
│   └── seeders/             # Database seeders
├── public/
├── routes/
├── storage/
├── tests/                   # Unit, Feature, (E2E) tests
└── .env
```

## 7. API Endpoints (Example)

Once the application is running, you can interact with the following example endpoint:

**Place a New Order**

- **Method**: `POST`
- **URL**: `/api/orders`
- **Headers**:
  - `Accept: application/json`
  - `Content-Type: application/json`
- **Body (JSON Example)**:
  ```json
  {
      "cart_items": [
          {
              "product_id": "YOUR_PRODUCT_A_UUID",
              "quantity": 2
          },
          {
              "product_id": "YOUR_PRODUCT_B_UUID",
              "quantity": 1
          }
      ]
  }
  ```
  *(Replace `YOUR_PRODUCT_A_UUID` and `YOUR_PRODUCT_B_UUID` with actual product IDs from your seeded data.)*

- **Success Response**: `HTTP/1.1 201 Created`
  ```json
  {
      "message": "Order placed successfully.",
      "order": {
          "order_id": "a1b2c3d4-e5f6-7890-1234-567890abcdef",
          "total_amount": 69.48,
          "status": "pending",
          "message": "Your order has been placed successfully."
      }
  }
  ```

- **Error Response**: `HTTP/1.1 400 Bad Request`
  ```json
  {
      "message": "Failed to place order.",
      "error": "Insufficient stock for product Sample Product A"
  }
  ```

## 8. Deployment & Infrastructure as Code (IaC)

This project is designed with IaC principles for streamlined deployment to cloud environments (e.g., AWS, GCP, Azure).

- **Terraform**: Used to provision and manage cloud infrastructure resources such as:
  - VPCs, Subnets, Security Groups, Load Balancers (ALB).
  - Container Orchestration (e.g., AWS ECS/EKS clusters for containerized Laravel applications).
  - Managed Databases (e.g., AWS RDS for MySQL/PostgreSQL).
  - Managed Caching (e.g., AWS ElastiCache for Redis).
  - Storage (e.g., AWS S3 for static assets).
- **Docker**: The Laravel application is containerized using `Dockerfile`, ensuring consistent runtime environments.
- **CI/CD Pipeline (GitLab CI/CD)**:
  1. Code pushes trigger automated tests (PHPUnit).
  2. Successful tests trigger Docker image builds.
  3. Docker images are pushed to a container registry (e.g., AWS ECR).
  4. Terraform applies infrastructure changes (e.g., updating ECS task definitions).
  5. Automated database migrations (`php artisan migrate --force`) are run during deployment.
  6. Deployment strategies like Blue/Green or Canary deployments can be implemented for zero-downtime updates.

**Example CI/CD Configuration**: See [`.gitlab-ci.yml`](#) for automated testing, building, and deployment with database migrations.

## 9. Monitoring & Observability

While specific tools are not directly integrated into the codebase in this example, the architecture supports:

- **Centralized Logging**: Laravel logs can be configured to output JSON, easily ingestible by centralized logging systems (e.g., ELK Stack, AWS CloudWatch Logs, Grafana Loki).
- **Application Performance Monitoring (APM)**: Integration with APM tools (e.g., New Relic, Datadog, Prometheus + Grafana) to monitor application response times, error rates, database queries, and queue performance.
- **Error Tracking**: Services like Sentry or Bugsnag can be easily integrated to capture and report application exceptions with detailed context.

## 10. Testing

The project emphasizes automated testing:

- **Unit Tests (`tests/Unit`)**: Focus on testing the Domain and Application layers in isolation, without bootstrapping the Laravel framework or connecting to a database.
- **Feature Tests (`tests/Feature`)**: Test specific features of the application, involving the Laravel framework, but typically using an in-memory database.
- **Code Quality**: Tools like PHPStan (static analysis) and PHP-CS-Fixer (code style) can be integrated into the CI pipeline.

To run tests:
```bash
docker-compose exec app php artisan test
```

## 11. Contributing

Contributions are welcome! Please feel free to open issues or submit pull requests.

## 12. License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT). This project is also open-sourced under the MIT license.