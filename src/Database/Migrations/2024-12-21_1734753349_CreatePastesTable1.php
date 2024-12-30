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
                hash CHAR(8) NOT NULL UNIQUE,
                title VARCHAR(50) NULL,
                content TEXT NOT NULL,
                language CHAR(20) NOT NULL,
                is_public TINYINT(1) NOT NULL DEFAULT 1,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                expires_at DATETIME NOT NULL DEFAULT "9999-12-31 23:59:59",
                INDEX idx_paste_timeline (is_public, created_at, expires_at)
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
