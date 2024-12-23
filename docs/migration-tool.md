# Custom Migration Tool

A custom command-line tool for managing database schema and seeding data into the database. Similar to Laravel's migration system, but simplified for this specific application's needs.

## Command Structure

All commands are executed through a central entry point (`console`):
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

## Implementation Details

The migrations table functions as a stack to manage database migrations and rollbacks. Its structure includes the following columns:
- id: Auto-incrementing primary key
- filename: Migration file name in format `{YYYY-MM-DD}{UNIX_TIMESTAMP}{FILENAME}.php`

![Screenshot 2024-12-22 at 19 37 23](https://github.com/user-attachments/assets/31617b5b-bcde-4880-9bdb-30aec2596886)

When generating a migration file, a boilerplate class is automatically created with `up()` and `down()` methods for applying and rolling back changes, respectively.  
This functionality is similar to Laravel's make:migration [artisan command](https://laravel.com/docs/7.x/migrations).

For example, running the command `php console code-gen migration --name CreateUsersTable` will generate the following boilerplate code:
```php
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






