<?php

declare(strict_types=1);

namespace Slink\Image\Application\Service\Sanitizer\Rule;

final class ScriptRemovalRule extends AbstractRegexSanitizationRule {
    
    public function getName(): string {
        return 'script_removal';
    }
    
    public function getPriority(): int {
        return 10;
    }
    
    protected function getPatterns(): array {
        return [
            '/<script[^>]*>.*?<\/script>/is',
            '/<script[^>]*\/>/is',
        ];
    }
}
