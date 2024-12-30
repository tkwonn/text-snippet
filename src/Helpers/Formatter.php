<?php

namespace Helpers;

class Formatter
{
    public static function formatBytes(int $bytes): string
    {
        $base = 1024;
        $units = ['KB', 'MB', 'GB'];
        $unitIndex = 0;

        $value = $bytes / $base;

        while ($value >= $base && $unitIndex < count($units) - 1) {
            $value /= $base;
            $unitIndex++;
        }

        return sprintf('%.2f%s', $value, $units[$unitIndex]);
    }
}
