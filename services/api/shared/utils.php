<?php

/**
 * Global helper functions
 * 
 * This file contains utility functions that are used across the application.
 * It's autoloaded via Composer's files autoload mechanism.
 */

if (!function_exists('convertSizeToBytes')) {
    /**
     * Convert a size string (e.g., "15M", "500k") to bytes
     * 
     * @param string $size Size string in format: number + unit (k|M)
     * @return int Size in bytes
     * @throws InvalidArgumentException If the size format is invalid
     */
    function convertSizeToBytes(string $size): int {
        if (!preg_match('/^(\d+)([kM])$/', $size, $matches)) {
            throw new InvalidArgumentException(
                "Invalid size format: {$size}. Expected format: number followed by 'k' or 'M' (e.g., '15M', '500k')"
            );
        }
        
        $value = (int) $matches[1];
        $unit = $matches[2];
        
        return match ($unit) {
            'k' => $value * 1024,
            'M' => $value * 1024 * 1024,
            default => throw new InvalidArgumentException("Unsupported size unit: {$unit}")
        };
    }
}
