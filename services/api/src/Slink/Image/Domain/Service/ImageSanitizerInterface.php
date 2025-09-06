<?php

declare(strict_types=1);

namespace Slink\Image\Domain\Service;

use Symfony\Component\HttpFoundation\File\File;

interface ImageSanitizerInterface {
    /**
     * Sanitizes content to remove potentially malicious scripts and elements
     */
    public function sanitize(string $content): string;
    
    /**
     * Sanitizes file in-place and returns the sanitized file
     * 
     * @template T of File
     * @param T $file
     * @return T
     */
    public function sanitizeFile(File $file): File;
    
    /**
     * Checks if the given MIME type requires sanitization
     */
    public function requiresSanitization(?string $mimeType): bool;
}
