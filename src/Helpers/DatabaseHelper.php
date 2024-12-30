<?php

namespace Helpers;

use Carbon\Carbon;
use Database\MySQLWrapper;
use Exception;

class DatabaseHelper
{
    private static ?MySQLWrapper $db = null;

    private const TIMEZONE = 'America/Los_Angeles';
    private const DATETIME_FORMAT = 'Y-m-d H:i:s';
    private const NEVER_EXPIRES = '9999-12-31 23:59:59';

    private static function getDb(): MySQLWrapper
    {
        if (!self::$db) {
            self::$db = new MySQLWrapper();
        }

        return self::$db;
    }

    /**
     * @throws Exception When failed to prepare statement
     * @throws Exception When failed to execute statement
     */
    public static function create(
        string $content,
        string $title,
        string $language,
        int $isPublic,
        string $expiresAt
    ): array {
        $db = self::getDb();

        $stmt = $db->prepare(
            'INSERT INTO pastes (hash, title, content, language, is_public, created_at, expires_at)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        if (!$stmt) {
            throw new Exception('Failed to prepare statement: ' . $db->error);
        }

        $hash = HashIdGenerator::generateHashId();
        $createdAt = Carbon::now()->format(self::DATETIME_FORMAT);
        $expiresAt = DateCalculator::getExpirationDate($expiresAt);

        $stmt->bind_param(
            'ssssiss',
            $hash,
            $title,
            $content,
            $language,
            $isPublic,
            $createdAt,
            $expiresAt
        );

        if (!$stmt->execute()) {
            throw new Exception('Failed to execute statement: ' . $stmt->error);
        }

        return [
            'hash' => $hash,
        ];
    }

    /**
     * @throws Exception When failed to prepare statement
     * @throws Exception When failed to execute statement
     */
    public static function findByHash(string $hash): ?array
    {
        $db = self::getDb();
        $stmt = $db->prepare(
            'SELECT hash, title, content, language, is_public, created_at, expires_at, LENGTH(content) AS size
             FROM pastes
             WHERE hash = ?'
        );
        if (!$stmt) {
            throw new Exception('Failed to prepare statement: ' . $db->error);
        }

        $stmt->bind_param('s', $hash);
        if (!$stmt->execute()) {
            throw new Exception('Failed to execute statement: ' . $stmt->error);
        }

        $result = $stmt->get_result();
        $paste = $result->fetch_assoc();
        if (!$paste) {
            return null;
        }

        if (Carbon::parse($paste['expires_at'])->isPast()) {
            return [
                'expired' => true,
            ];
        }

        return [
            'hash' => $paste['hash'],
            'title' => $paste['title'],
            'content' => $paste['content'],
            'language' => $paste['language'],
            'is_public' => $paste['is_public'] ? 'public' : 'unlisted',
            'created_at' => Carbon::parse($paste['created_at'])->setTimezone(self::TIMEZONE)->format(self::DATETIME_FORMAT),
            'expires_at' => $paste['expires_at'] === self::NEVER_EXPIRES ? 'never' : Carbon::parse($paste['expires_at'])->setTimezone(self::TIMEZONE)->format(self::DATETIME_FORMAT),
            'size' => Formatter::formatBytes($paste['size']),
        ];
    }

    /**
     * @throws Exception When failed to prepare statement
     * @throws Exception When failed to execute statement
     */
    public static function findRecent(): array
    {
        $db = self::getDb();
        $stmt = $db->prepare(
            'SELECT hash, title, language, created_at, expires_at, LENGTH(content) AS size
            FROM pastes
            WHERE is_public = 1
            AND expires_at > NOW()
            ORDER BY created_at DESC
            LIMIT 10'
        );
        if (!$stmt) {
            throw new Exception('Failed to prepare statement: ' . $db->error);
        }
        if (!$stmt->execute()) {
            throw new Exception('Failed to execute statement: ' . $stmt->error);
        }
        $results = $stmt->get_result();
        $rows = $results->fetch_all(MYSQLI_ASSOC);
        if (!$rows) {
            return [];
        }

        return array_map(function ($paste) {
            return [
                'hash' => $paste['hash'],
                'title' => $paste['title'],
                'language' => $paste['language'],
                'created_at' => Carbon::parse($paste['created_at'])
                    ->setTimezone(self::TIMEZONE)
                    ->diffForHumans(),
                'size' => Formatter::formatBytes($paste['size']),
            ];
        }, $rows);
    }
}
