<?php

namespace Helpers;

use Carbon\Carbon;
use Database\MySQLWrapper;
use Exception;
use Utils\DateCalculator;
use Utils\Formatter;
use Utils\HashIdGenerator;

class DatabaseHelper
{
    private static ?MySQLWrapper $db = null;

    private const TIMEZONE = 'America/Los_Angeles';
    private const DATETIME_FORMAT = 'Y-m-d H:i:s';
    private const DEFAULT_TITLE = 'Untitled';
    private const DEFAULT_LANGUAGE = 'plaintext';
    private const DEFAULT_EXPOSURE = 'public';
    private const DEFAULT_EXPIRATION = 'Never';
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
    public static function createPaste(
        string $content,
        ?string $title = self::DEFAULT_TITLE,
        string $language = self::DEFAULT_LANGUAGE,
        string $expiresAt = self::DEFAULT_EXPIRATION,
        string $exposure = self::DEFAULT_EXPOSURE
    ): array {
        $db = self::getDb();

        $stmt = $db->prepare(
            'INSERT INTO pastes (hash_id, title, content, language, exposure, created_at, expires_at)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        if (!$stmt) {
            throw new Exception('Failed to prepare statement: ' . $db->error);
        }

        $hashId = HashIdGenerator::generateHashId();
        $createdAt = Carbon::now()->format(self::DATETIME_FORMAT);
        $expiresAt = DateCalculator::getExpirationDate($expiresAt);

        $stmt->bind_param(
            'sssssss',
            $hashId,
            $title,
            $content,
            $language,
            $exposure,
            $createdAt,
            $expiresAt
        );

        if (!$stmt->execute()) {
            throw new Exception('Failed to execute statement: ' . $stmt->error);
        }

        return [
            'hash_id' => $hashId,
        ];
    }

    /**
     * @throws Exception When failed to prepare statement
     * @throws Exception When failed to execute statement
     */
    public static function getPasteByHashId(string $hashId): ?array
    {
        $db = self::getDb();
        $stmt = $db->prepare(
            'SELECT hash_id, title, content, language, exposure, created_at, expires_at, LENGTH(content) AS size
             FROM pastes
             WHERE hash_id = ?'
        );
        if (!$stmt) {
            throw new Exception('Failed to prepare statement: ' . $db->error);
        }

        $stmt->bind_param('s', $hashId);
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
            'hash_id' => $paste['hash_id'],
            'title' => $paste['title'],
            'content' => $paste['content'],
            'language' => $paste['language'],
            'exposure' => $paste['exposure'],
            'created_at' => Carbon::parse($paste['created_at'])->setTimezone(self::TIMEZONE)->format(self::DATETIME_FORMAT),
            'expires_at' => $paste['expires_at'] === self::NEVER_EXPIRES ? 'never' : Carbon::parse($paste['expires_at'])->setTimezone(self::TIMEZONE)->format(self::DATETIME_FORMAT),
            'size' => Formatter::formatBytes($paste['size']),
        ];
    }

    /**
     * @throws Exception When failed to prepare statement
     * @throws Exception When failed to execute statement
     */
    public static function getRecentPublicPastes(): array
    {
        $db = self::getDb();
        $stmt = $db->prepare(
            'SELECT hash_id, title, language, created_at, expires_at, LENGTH(content) AS size
            FROM pastes
            WHERE exposure = "public"
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
                'hash_id' => $paste['hash_id'],
                'title' => $paste['title'],
                'language' => $paste['language'],
                'created_at' => Carbon::parse($paste['created_at'])
                    ->setTimezone(self::TIMEZONE)
                    ->diffForHumans(),
                'size' => Formatter::formatBytes($paste['size']),
            ];
        }, $rows);
    }

    /**
     * @throws Exception When failed to prepare statement
     * @throws Exception When failed to execute statement
     */
    public static function cleanupExpiredPastes(): int
    {
        $db = self::getDb();
        $stmt = $db->prepare(
            'DELETE FROM pastes
            WHERE expires_at < NOW()'
        );
        if (!$stmt) {
            throw new Exception('Failed to prepare statement: ' . $db->error);
        }
        if (!$stmt->execute()) {
            throw new Exception('Failed to execute statement: ' . $stmt->error);
        }

        return $stmt->affected_rows;
    }
}
