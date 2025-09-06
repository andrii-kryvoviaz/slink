<?php

declare(strict_types=1);

namespace Slink\Image\Application\Service\Sanitizer\Rule;

interface SanitizationRuleInterface {
    /**
     * Apply sanitization rule to SVG content
     */
    public function sanitize(string $svgContent): string;
    
    /**
     * Get rule name for debugging/logging purposes
     */
    public function getName(): string;
    
    /**
     * Get rule priority (lower number = higher priority)
     */
    public function getPriority(): int;
}
