<?php

namespace Database\Migrations;

use Database\SchemaMigration;

class CreatePastesTable1 implements SchemaMigration
{
    public function up(): array
    {
        return [
            'CREATE TABLE pastes (
                id BIGINT PRIMARY KEY AUTO_INCREMENT,
                hash_id VARCHAR(8) NOT NULL UNIQUE,
                title VARCHAR(255) NULL,
                content TEXT NOT NULL,
                language VARCHAR(50) NOT NULL,
                exposure ENUM("public", "unlisted") NOT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                expires_at DATETIME NULL
            );',
        ];
    }

    public function down(): array
    {
        return [
            'DROP TABLE IF EXISTS pastes;',
        ];
    }
}
