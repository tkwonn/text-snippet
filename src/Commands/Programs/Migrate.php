<?php

namespace Commands\Programs;

use Commands\AbstractCommand;
use Commands\Argument;
use Database\MySQLWrapper;
use Exception;

class Migrate extends AbstractCommand
{
    protected static ?string $alias = 'migrate';

    protected static function getDescription(): string
    {
        return 'Manages database migrations, including running new migrations and rolling back existing ones.';
    }

    protected static function getExamples(): string
    {
        return <<<EXAMPLES
  # Create migrations table if not exists
  php console migrate --init
  
  # Run migration
  php console migrate

  # Rollback the last migration
  php console migrate --rollback

  # Rollback the last 3 migrations
  php console migrate --rollback 3
EXAMPLES;
    }

    public static function getArguments(): array
    {
        return [
            (new Argument('init'))->description("Create the migrations table if it doesn't exist.")->required(false)->allowAsShort(true),
            (new Argument('rollback'))->description('Roll backwards. An integer n may also be provided to rollback n times.')->required(false)->allowAsShort(true),
        ];
    }

    /**
     * @throws Exception When failed to create migration table
     */
    public function execute(): int
    {
        $rollback = $this->getArgumentValue('rollback');

        if ($this->getArgumentValue('init')) {
            $this->createMigrationsTable();
        }

        if ($rollback === false) {
            $this->log('Starting migration...');
            $this->migrate();
        } else {
            $rollbackN = $rollback === true ? 1 : (int) $rollback;
            $this->log('Running rollback...');
            $this->rollback($rollbackN);
        }

        return 0;
    }

    /**
     * @throws Exception When mysqli connection fails
     * @throws Exception When failed to create migration table
     */
    private function createMigrationsTable(): void
    {
        $this->log('Creating migrations table if necessary...');

        $mysqli = new MySQLWrapper();
        if ($mysqli->connect_error) {
            throw new Exception('MySQL connection failed: ' . $mysqli->connect_error);
        }

        $result = $mysqli->query('
            CREATE TABLE IF NOT EXISTS migrations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                filename VARCHAR(255) NOT NULL
            );
        ');

        if ($result === false) {
            throw new Exception('Failed to create migration table.');
        }

        $this->log('Done setting up migration tables.');
    }

    /**
     * @throws Exception When migration directory is not found
     * @throws Exception When migration class does not have queries
     */
    private function migrate(): void
    {
        $this->log('Running migrations...');

        $lastMigration = $this->getLastMigration();
        $allMigrations = $this->getAllMigrationFiles('asc');
        /*
         * If a previous migration was found in the database, start from the next migration in the list.
         * Otherwise, begin with the first migration file (index 0).
         */
        $startIndex = ($lastMigration) ? array_search($lastMigration, $allMigrations) + 1 : 0;

        for ($i = $startIndex; $i < count($allMigrations); $i++) {
            $filename = $allMigrations[$i];

            include_once $filename;

            $migrationClass = $this->getClassnameFromMigrationFilename($filename);
            $migration = new $migrationClass();
            $this->log(sprintf('Processing up migration for %s', $migrationClass));
            $queries = $migration->up();
            if (empty($queries)) {
                throw new Exception('Must have queries to run for . ' . $migrationClass);
            }

            $this->processQueries($queries);
            $this->insertMigration($filename);
        }

        $this->log("Migration ended...\n");
    }

    /**
     * @throws Exception When migration filename does not match expected format
     */
    private function getClassnameFromMigrationFilename(string $filename): string
    {
        /*
         * Capture the filename that does not contain an underscore and ends with .php
         * Why matches[1]? Because we need the filename without the .php extension (string that matched the capture group).
         */
        if (preg_match('/([^_]+)\.php$/', $filename, $matches)) {
            return sprintf("%s\%s", 'Database\Migrations', $matches[1]);
        } else {
            throw new Exception('Unexpected migration filename format: ' . $filename);
        }
    }

    private function getLastMigration(): ?string
    {
        $mysqli = new MySQLWrapper();

        $query = 'SELECT filename FROM migrations ORDER BY id DESC LIMIT 1';

        $result = $mysqli->query($query);

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();

            return $row['filename'];
        }

        return null;
    }

    /**
     * @throws Exception When migrations directory is not found
     */
    private function getAllMigrationFiles(string $order = 'desc'): array
    {
        $directory = realpath(__DIR__ . '/../../Database/Migrations');
        $this->log('Looking for migration files in directory: ' . $directory);

        if (!$directory || !is_dir($directory)) {
            throw new Exception('Migrations directory not found or is not a directory: ' . $directory);
        }
        $allFiles = glob($directory . '/*.php');

        usort($allFiles, function ($a, $b) use ($order) {
            $compareResult = strcmp($a, $b);

            return ($order === 'desc') ? -$compareResult : $compareResult;
        });

        return $allFiles;
    }

    /**
     * @throws Exception When query failed
     */
    private function processQueries(array $queries): void
    {
        $mysqli = new MySQLWrapper();
        foreach ($queries as $query) {
            $result = $mysqli->query($query);
            if ($result === false) {
                throw new Exception(sprintf('Query {%s} failed.', $query));
            } else {
                $this->log('Ran query: ' . $query);
            }
        }
    }

    /**
     * @throws Exception When prepare statement failed
     * @throws Exception When execute statement failed
     */
    private function insertMigration(string $filename): void
    {
        $mysqli = new MySQLWrapper();
        $statement = $mysqli->prepare('INSERT INTO migrations (filename) VALUES (?)');
        if (!$statement) {
            throw new Exception('Prepare failed: (' . $mysqli->errno . ') ' . $mysqli->error);
        }

        $statement->bind_param('s', $filename);

        if (!$statement->execute()) {
            throw new Exception('Execute failed: (' . $statement->errno . ') ' . $statement->error);
        }

        $statement->close();
    }

    /**
     * @throws Exception When migration directory is not found
     * @throws Exception When migration class does not have queries
     */
    private function rollback(int $n = 1): void
    {
        $this->log("Rolling back {$n} migration(s)...");

        $lastMigration = $this->getLastMigration();
        $allMigrations = $this->getAllMigrationFiles('asc');

        $lastMigrationIndex = array_search($lastMigration, $allMigrations);

        if ($lastMigrationIndex === false) {
            $this->log('Could not find the last migration ran: ' . $lastMigration);

            return;
        }

        $count = 0;
        for ($i = $lastMigrationIndex; $count < $n && $i >= 0; $i--) {
            $filename = $allMigrations[$i];

            $this->log("Rolling back: {$filename}");

            include_once $filename;

            $migrationClass = $this->getClassnameFromMigrationFilename($filename);
            $migration = new $migrationClass();

            $queries = $migration->down();
            if (empty($queries)) {
                throw new Exception('Must have queries to run for . ' . $migrationClass);
            }

            $this->processQueries($queries);
            $this->removeMigration($filename);
            $count++;
        }

        $this->log("Rollback completed.\n");
    }

    /**
     * @throws Exception When prepare statement failed
     * @throws Exception When execute statement failed
     */
    private function removeMigration(string $filename): void
    {
        $mysqli = new MySQLWrapper();
        $statement = $mysqli->prepare('DELETE FROM migrations WHERE filename = ?');

        if (!$statement) {
            throw new Exception('Prepare failed: (' . $mysqli->errno . ') ' . $mysqli->error);
        }

        $statement->bind_param('s', $filename);
        if (!$statement->execute()) {
            throw new Exception('Execute failed: (' . $statement->errno . ') ' . $statement->error);
        }

        $statement->close();
    }
}
