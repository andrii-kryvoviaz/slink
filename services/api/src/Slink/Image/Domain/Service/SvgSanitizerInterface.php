<?php

declare(strict_types=1);

namespace Slink\Image\Domain\Service;

use SplFileInfo;

interface SanitizerInterface {
    /**
     * Sanitizes content to remove potentially malicious scripts and elements
     */
    public function sanitize(string $content): string;
    
    /**
     * Sanitizes file in-place and returns the sanitized file
     */
    public function sanitizeFile(SplFileInfo $file): SplFileInfo;
    
    /**
     * Checks if the given MIME type requires sanitization
     */
    public function requiresSanitization(?string $mimeType): bool;
}
