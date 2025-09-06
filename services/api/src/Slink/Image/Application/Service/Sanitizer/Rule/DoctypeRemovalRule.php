<?php

declare(strict_types=1);

namespace Slink\Image\Application\Service\Sanitizer\Rule;

final class DoctypeRemovalRule extends AbstractRegexSanitizationRule {
    
    public function getName(): string {
        return 'doctype_removal';
    }
    
    public function getPriority(): int {
        return 5;
    }
    
    protected function getPatterns(): array {
        return [
            '/<!DOCTYPE[^>]*>/is',
            '/<!ENTITY[^>]*>/is',
            '/&\w+;/i',
        ];
    }
}
