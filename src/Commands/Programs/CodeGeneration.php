<?php

namespace Commands\Programs;

use Commands\AbstractCommand;
use Commands\Argument;

class CodeGeneration extends AbstractCommand
{
    protected static ?string $alias = 'code-gen';
    protected static bool $requiredCommandValue = true;

    protected static function getDescription(): string
    {
        return 'Generate boilerplate code files based on the specified type (see Values below).';
    }

    protected static function getExamples(): string
    {
        return <<<EXAMPLES
  # Generate a migration file
  php console code-gen migration --name CreateUsersTable

  # Generate a seeder file
  php console code-gen seeder --name UserSeeder
EXAMPLES;
    }

    protected static function getCommandValues(): array
    {
        return [
            'migration' => 'Generate a new database migration file',
            'seeder' => 'Generate a new database seeder file',
        ];
    }

    public static function getArguments(): array
    {
        return [
            (new Argument('name'))->description('Name of the file that is to be generated.')->required(false),
        ];
    }

    public function execute(): int
    {
        $codeGenType = $this->getCommandValue();
        $this->log('Generating code for.......' . $codeGenType);

        if ($codeGenType === 'migration') {
            $migrationName = $this->getArgumentValue('name');
            $this->generateMigrationFile($migrationName);
        } elseif ($codeGenType === 'seeder') {
            $seederName = $this->getArgumentValue('name');
            $this->generateSeederFile($seederName);
        }

        return 0;
    }

    private function generateMigrationFile(string $migrationName): void
    {
        $filename = sprintf(
            '%s_%s_%s.php',
            date('Y-m-d'),
            time(),
            $migrationName
        );

        $migrationContent = $this->getMigrationContent($migrationName);

        $path = sprintf('%s/../../Database/Migrations/%s', __DIR__, $filename);

        file_put_contents($path, $migrationContent);
        $this->log("Migration file {$filename} has been generated!");
    }

    private function getMigrationContent(string $migrationName): string
    {
        $className = $this->pascalCase($migrationName);

        return <<<MIGRATION
<?php
namespace Database\Migrations;

use Database\SchemaMigration;

class {$className} implements SchemaMigration
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
MIGRATION;
    }

    private function generateSeederFile(string $seederName): void
    {
        if (!str_ends_with($seederName, 'Seeder')) {
            $seederName .= 'Seeder';
        }

        $filename = sprintf('%s.php', $seederName);
        $seederContent = $this->getSeederContent($seederName);

        $path = sprintf('%s/../../Database/Seeds/%s', __DIR__, $filename);

        file_put_contents($path, $seederContent);
        $this->log("Seeder file {$filename} has been generated!");
    }

    private function getSeederContent(string $seederName): string
    {
        return <<<SEEDER
<?php
namespace Database\Seeds;

use Database\AbstractSeeder;

class {$seederName} extends AbstractSeeder
{
    protected ?string \$tableName = null;

    protected array \$tableColumns = [];

    public function createRowData(): array
    {
        return [];
    }
}
SEEDER;
    }

    private function pascalCase(string $string): string
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
    }
}
