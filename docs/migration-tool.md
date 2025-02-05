# Custom Migration Tool

A custom database migration and seeding tool built with PHP, providing a command-line interface for managing database schema and test data.

## Directory Structure

Below is the relevant directory structure for this migration tool (some files omitted for clarity).

```php
src/
├── Commands/
│   ├── Programs/
│   │   ├── CodeGeneration.php
│   │   ├── Migrate.php
│   │   └── Seed.php
│   ├── AbstractCommand.php # Base class for all commands
│   ├── Argument.php # Builder class that defines the arguments available for a command.
│   ├── Command.php # An interface defining the methods that all commands have.
│   └── registry.php # A registry where commands are registered, and the console reads from these.
├── Database/
│   ├── Migrations/ # Directory where all migration files are stored.
│   │   └── 2021-09-01-1734753349_CreateUsersTable.php
│   ├── Seeders/ # Directory where all seeder files are stored.
│   │   └── UserSeeder.php
│   ├── AbstractSeeder.php # Base class for all seeders
│   ├── MySQLWrapper.php # OOP wrapper for MySQLi
│   ├── SchemaMigration.php # An interface that defines the methods that all migration files must implement.
│   └── Seeder.php # An interface that defines the methods that all seeders must implement.
└── console # Entry point for all command line programs.
```

## Command System

All commands are executed through a central entry point `console`.
```bash
php console {program} {program_value} {--option1} {option1_value} ... {--optionN} {optionN_value}
```

### Available Commands

1. `code-gen`: Generate boilerplate code
2. `migrate`: Run database migrations
3. `seed`: Seed the database with test data

<br>

```sh
$ php console code-gen --help

Command: code-gen <value>

Description:
  Generate boilerplate code files based on the specified type (see Values below).
  
Values:
  migration    Generate a new database migration file
  seeder       Generate a new database seeder file
  
Arguments:
  --name: Name of the file that is to be generated. (Optional)
  
Examples:
  # Generate a migration file
  php console code-gen migration --name CreateUsersTable
  # Generate a seeder file
  php console code-gen seeder --name UserSeeder
```

```sh
$ php console migrate --help

Command: migrate

Description:
  Manages database migrations, including running new migrations and rolling back existing ones.
  
Arguments:
  --init (-i): Create the migrations table if it does not exist. (Optional)
  --rollback (-r): Roll backwards. An integer n may also be provided to rollback n times. (Optional)
  
Examples:
  # Create migrations table if not exists
  php console migrate --init
  # Run migration
  php console migrate
  # Rollback the last migration
  php console migrate --rollback
  # Rollback the last 3 migrations
  php console migrate --rollback 3
```

```shell
$ php console seed --help
 
Command: seed

Description:
  Seeds the database with test data.
  
Arguments:
  --class (-c): Name of the seeder class to run. (Optional)
  
Examples:
  # Run all seeders
  php console seed
  # Run a specific seeder
  php console seed --class UserSeeder
```

## How it works

The `migrations` table works as a stack to manage database migrations and rollbacks. Its structure includes the following columns:
- `id`: Auto-incrementing primary key
- `filename`: Migration file name in format `{YYYY-MM-DD}{UNIX_TIMESTAMP}{FILENAME}.php`

![CleanShot 2024-12-29 at 18 33 48](https://github.com/user-attachments/assets/e11e66c1-fccb-4249-90ee-1648f8f623cd)

For example, running the command `php console code-gen migration --name CreateUsersTable` will generate the following boilerplate code.
```php
// File: src/Database/Migrations/2021-09-01-1734753349_CreateUsersTable.php
<?php

namespace Database\Migrations;

use Database\SchemaMigration;

class CreateUsersTable implements SchemaMigration
{
    public function up(): array
    {
        return [];
    }

    public function down(): array
    {
        return [];
    }
}
```






