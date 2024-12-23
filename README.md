# Text Snippet Sharing Service

[![GitHub last commit](https://img.shields.io/github/last-commit/tkwonn/text-snippet?color=chocolate)](https://github.com/tkwonn/text-snippet/commits/)

## What is this

Similar to Pastebin, this web application allows users to share plain text and code snippets without requiring a user account.
It's useful in the following situations:
- Save frequently used code
- Share code snippets with others
- Refer to code shared by others

**URL**: [text-snippet.taesokkwon.com](https://text-snippet.taesokkwon.com)

<br>

## Table of Contents

1. [Demo](#demo)
2. [Built with](#built-with)
3. [ER Diagram](#er-diagram)
4. [Cloud Architecture Diagram](#cloud-architecture-diagram)
5. [Security Measures](#security-measures)
   1. [HTTP Method Restrictions](#http-method-restrictions)
   2. [Input Sanitization and Character Escaping](#input-sanitization-and-character-escaping)
   3. [Rate Limiting for DoS Protection](#rate-limiting-for-dos-protection)
   4. [Secure URL generation](#secure-url-generation)
6. [CI/CD](#cicd)
   1. [Continuous Integration](#continuous-integration)
   2. [Continuous Deployment](#continuous-deployment)
7. [How to use](#how-to-use)

<br>

## Demo

Step1. Create new paste

Step2. Browse paste

Note: Expired page (cronjobについて)

<br>

## Built with

| **Category** | **Technology**            |
|--------------|---------------------------|
| VM           | Amazon EC2                |
| Web server   | Nginx                     |
| Frontend     | HTML, JavaScript, Bootstrap CSS |
| Backend      | PHP 8.2                   |
| Database     | MySQL 8.0                 |
| Middleware   | Custom-built migration tool (with link) |
| CI/CD        | GitHub Actions            |
| Tools        | Monaco Editor             |
| Container    | Docker (only for local development) |

<br>

## ER Diagram

![Screenshot 2024-12-22 at 15 04 40](https://github.com/user-attachments/assets/c3f689d7-3e92-46d1-a89b-2ccb407e9cfa)

The `pastes` table uses a `hash_id` column to uniquely identify each snippet. When a user creates a new paste, `hash_id` is generated with a random, unique string that becomes part of the URL (`https://{domain}/{hash_id}`). A UNIQUE constraint on this column ensures that each paste has a distinct identifier.

The `migrations` table, which contains `id` and `filename` columns, is required for our custom-built migration tool. This table is utilized as a stack to enable database migrations and rollbacks.

<br>

## Cloud Architecture Diagram

<br>

## Security Measures

### HTTP Method Restrictions

The application checks content size before submission. If the content size exceeds the limit, the application will return an error message. This feature prevents users from submitting large amounts of data.

```php
// php ini configuration
post_max_size = 8M
memory_limit = 128M
max_input_time = 60
max_execution_time = 30
```

![Screenshot 2024-12-21 at 19 14 41](https://github.com/user-attachments/assets/130c5ddb-37d6-49db-a39e-bae0ac512b63)

For testing, `post_max_size` was intentionally set to a low limit (2KB) to verify the validation functionality. In production, the limit is set to 8MB to accommodate larger code snippets while still protecting against potential abuse.

### Input Sanitization and Character Escaping

- Special characters (`\n`, `\t`, `\'`, `\"`, `\`) are properly escaped/unescaped using PHP's `json_encode()` and `json_decode()` methods.
- All database inputs are parameterized using `mysqli` prepared statements to prevent SQL injection.
- HTML special characters are escaped using `htmlspecialchars()` when displaying titles and metadata.

### Rate Limiting for DoS Protection

The application limits requests based on the client's IP address. 

```
// Nginx configuration

limit_req_zone $binary_remote_addr zone=one:10m rate=10r/m;

server {
    listen 80;
    server_name localhost;
    root /var/www/html/public;
    index index.php index.html index.htm;

    ...
    
    location = /api/paste {
        limit_req zone=one;
        try_files $uri $uri/ /index.php?$query_string;
    }

    ...
}
```

In the demonstration video below, I set a test limit of 2 requests per minute to show the rate limiting in action. In production, this will be adjusted to 10 requests per minute (with burst=5) to balance between service availability and security.

https://github.com/user-attachments/assets/f4a1ed0f-2970-4c34-96a1-9cf6d8369c96

### Secure URL generation

The implementation follows cryptographic best practices recommended by [Latacora's Cryptographic Right Answers](https://www.latacora.com/blog/2018/04/03/cryptographic-right-answers/) using PHP's [random_bytes()](https://www.php.net/manual/en/function.random-bytes.php) function, which provides cryptographically secure random values by leveraging the OS's `/dev/urandom`.

The implementation uses:
- A character set of 64 characters for URL-safe encoding
- 8 characters length for the final hash
- Total possible combinations: 64⁸ = 281,474,976,710,656

With such a large number of possible combinations making collisions extremely unlikely, the implementation simply uses a database UNIQUE constraint without any additional collision handling logic.

<br>

## CI/CD

### Continuous Integration

- Dependency caching using Composer to speed up builds
- Code quality checks using PHP CS Fixer

### Continuous Deployment

- Secure AWS Authentication using OpenID Connect (short-lived tokens)
- Minimal IAM permissions to ensure secure cloud role operations
- AWS Systems Manager (SSM) for secure remote command execution (no direct SSH access or security group changes)

<br>

## How to use

This project uses Docker for local development, making it easy for anyone to run and test the application on their local machine.

1. Clone this repository
```bash
git clone https://github.com/yourusername/text-snippet.git
cd text-snippet
```

2. Setup the environment variables
```bash
# Copy the example environment file
cp .env.example .env

# Example of .env file content
DATABASE_HOST=mysql
DATABASE_NAME=pastes
DATABASE_USER=user
DATABASE_USER_PASSWORD=password
DATABASE_ROOT_PASSWORD=root_password
```

3. Build and run the containers
```bash
make build
make up

# Initialize the database
make db/migrate
make db/seed
```

The application will be available at `http://localhost:8080`



