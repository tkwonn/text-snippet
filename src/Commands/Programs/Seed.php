<?php

namespace Commands\Programs;

use Commands\AbstractCommand;
use Commands\Argument;
use Database\MySQLWrapper;
use Database\Seeder;
use Exception;

class Seed extends AbstractCommand
{
    protected static ?string $alias = 'seed';
    private static string $seedsDirectory = __DIR__ . '/../../Database/Seeds';

    protected static function getDescription(): string
    {
        return 'Seeds the database with test data.';
    }

    protected static function getExamples(): string
    {
        return <<<EXAMPLES
  # Run all seeders
  php console seed

  # Run a specific seeder
  php console seed --class UserSeeder
EXAMPLES;
    }

    public static function getArguments(): array
    {
        return [
            (new Argument('class'))->description('Name of the seeder class to run.')->required(false)->allowAsShort(true),
        ];
    }

    /**
     * @throws Exception
     */
    public function execute(): int
    {
        $className = $this->getArgumentValue('class');

        if ($className) {
            $this->runSpecificSeeder($className);
        } else {
            $this->runAllSeeders();
        }

        return 0;
    }

    /**
     * @throws Exception When seeder file is not found
     * @throws Exception When seeder class is not found
     * @throws Exception When seeder class is not a subclass of Seeder interface
     */
    private function runSpecificSeeder(string $className): void
    {
        $filePath = sprintf('%s/%s.php', self::$seedsDirectory, $className);
        $seederClass = sprintf('Database\\Seeds\\%s', $className);

        if (!file_exists($filePath)) {
            throw new Exception(sprintf('Seeder file not found in %s', $filePath));
        }
        include_once $filePath;

        if (!class_exists($seederClass)) {
            throw new Exception(sprintf("Seeder class '%s' not found", $seederClass));
        }

        if (!is_subclass_of($seederClass, Seeder::class)) {
            throw new Exception(sprintf(
                "'%s' must be a class that subclasses the Seeder interface",
                $className
            ));
        }

        $this->log("Running seeder: $className");
        $seeder = new $seederClass(new MySQLWrapper());
        $seeder->seed();
        $this->log("Completed seeder: $className");
    }

    /**
     * @throws Exception
     */
    public function runAllSeeders(): void
    {
        $this->log('Looking for seeders in: ' . realpath(self::$seedsDirectory));

        $files = scandir(self::$seedsDirectory);

        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $className = pathinfo($file, PATHINFO_FILENAME);
                $this->runSpecificSeeder($className);
            }
        }
    }
}
