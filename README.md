# Text Snippet Sharing Service

[![GitHub last commit](https://img.shields.io/github/last-commit/tkwonn/text-snippet?color=chocolate)](https://github.com/tkwonn/text-snippet/commits/)

<br>

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
4. [Architecture Diagram](#architecture-diagram)
5. [Features](#features)
6. [Security Measures](#security-measures)
7. [CI/CD](#cicd)
8. [How to use](#how-to-use)

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

The `hash_id` column in the `pastes` table is used to generate a unique URL for each snippet when users submit content.   
The URL format is `https://{domain}/{unique-string}`, where the unique-string value is stored in the database's `hash_id` column. 
This column has a UNIQUE constraint to ensure each snippet has a distinct URL.  

The `migrations` table, which contains id and filename columns, is required for our custom-built migration tool. These columns are utilized as a stack to enable database migrations and rollbacks.

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
- Monaco Editor handles the text content as read-only when viewing, preventing XSS attacks.
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

In the demonstration video below, I set a test limit of 2 requests per minute to show the rate limiting in action. In production, this will be adjusted to 10 requests per minute to balance between service availability and security.

https://github.com/user-attachments/assets/f4a1ed0f-2970-4c34-96a1-9cf6d8369c96

### Secure URL generation

- 暗号学的に安全な乱数を使用
- 64文字（2の6乗）の文字セットを使用し、効率的なビット使用
- 8文字で約2.8×10¹⁴の組み合わせ（64⁸）が可能

生成可能な一意のURL数：
`64⁸ = 281,474,976,710,656`

<br>

## CI/CD

### Continuous Integration (CI)

- Dependency caching using Composer to speed up builds
- Code quality checks using PHP CS Fixer

### Continuous Deployment (CD)

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



