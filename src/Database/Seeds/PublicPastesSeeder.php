<?php

namespace Database\Seeds;

use Carbon\Carbon;
use Database\AbstractSeeder;
use Utils\HashIdGenerator;

class PublicPastesSeeder extends AbstractSeeder
{
    protected ?string $tableName = 'pastes';
    protected array $tableColumns = [
        ['data_type' => 'string', 'column_name' => 'hash_id'],
        ['data_type' => 'string', 'column_name' => 'title'],
        ['data_type' => 'string', 'column_name' => 'content'],
        ['data_type' => 'string', 'column_name' => 'language'],
        ['data_type' => 'string', 'column_name' => 'exposure'],
        ['data_type' => 'Carbon', 'column_name' => 'created_at'],
        ['data_type' => 'Carbon', 'column_name' => 'expires_at'],
    ];

    public function createRowData(): array
    {
        $rows = [];

        $sampleSnippets = [
            [
                'title' => 'Hello World in Python',
                'content' => "print('Hello, World!')\n\n# This is a simple Python program\nname = input('Enter your name: ')\nprint(f'Hello, {name}!')",
                'language' => 'python',
            ],
            [
                'title' => 'Simple PHP Function',
                'content' => "<?php\n\nfunction calculateSum(array \$numbers): int {\n    return array_sum(\$numbers);\n}\n\n\$result = calculateSum([1, 2, 3, 4, 5]);\necho \$result;",
                'language' => 'php',
            ],
            [
                'title' => 'JavaScript Array Methods',
                'content' => "const numbers = [1, 2, 3, 4, 5];\n\nconst doubled = numbers.map(num => num * 2);\nconst evens = numbers.filter(num => num % 2 === 0);\nconst sum = numbers.reduce((acc, curr) => acc + curr, 0);\n\nconsole.log({ doubled, evens, sum });",
                'language' => 'javascript',
            ],
            [
                'title' => 'SQL Query Example',
                'content' => "SELECT users.name, orders.total\nFROM users\nINNER JOIN orders ON users.id = orders.user_id\nWHERE orders.status = 'completed'\nORDER BY orders.total DESC\nLIMIT 10;",
                'language' => 'sql',
            ],
            [
                'title' => 'Git Commands Cheatsheet',
                'content' => "# Basic Git Commands\ngit init\ngit add .\ngit commit -m 'Initial commit'\ngit push origin main\n\n# Branching\ngit branch feature/new-feature\ngit checkout -b feature/new-feature",
                'language' => 'markdown',
            ],
            [
                'title' => 'HTML Template',
                'content' => "<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n    <meta charset=\"UTF-8\">\n    <title>Sample Page</title>\n</head>\n<body>\n    <h1>Welcome</h1>\n    <p>This is a sample HTML template.</p>\n</body>\n</html>",
                'language' => 'html',
            ],
            [
                'title' => 'Docker Compose Example',
                'content' => "version: '3.8'\n\nservices:\n  app:\n    build: ./app\n    ports:\n      - '8080:80'\n    volumes:\n      - ./app:/var/www/html",
                'language' => 'yaml',
            ],
        ];

        foreach ($sampleSnippets as $snippet) {
            $rows[] = [
                HashIdGenerator::generateHashId(),
                $snippet['title'],
                $snippet['content'],
                $snippet['language'],
                'public',
                Carbon::now(),
                null,
            ];
        }

        return $rows;
    }
}
