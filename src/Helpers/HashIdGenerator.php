<?php

namespace Helpers;

class HashIdGenerator
{
    private const HASH_LENGTH = 32;
    private const URL_SAFE_CHARS = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz-_';

    public static function generateHashId(): string
    {
        $randomBytes = random_bytes(self::HASH_LENGTH);
        $hashId = '';

        for ($i = 0; $i < self::HASH_LENGTH; $i++) {
            $value = ord($randomBytes[$i]);
            // Using 0-63 to index the URL_SAFE_CHARS array
            $hashId .= self::URL_SAFE_CHARS[$value & (strlen(self::URL_SAFE_CHARS) - 1)];
        }

        return substr($hashId, 0, 8);
    }
}
