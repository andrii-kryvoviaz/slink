<?php

declare(strict_types=1);

namespace Slink\Image\Application\Service\Sanitizer\Rule;

final class EventHandlerRemovalRule extends AbstractRegexSanitizationRule {
    
    public function getName(): string {
        return 'event_handler_removal';
    }
    
    public function getPriority(): int {
        return 20;
    }
    
    protected function getPatterns(): array {
        return [
            '/\s+on\w+\s*=\s*["\'][^"\']*["\']/i',
            '/\s+on\w+\s*=\s*[^"\'\s][^\s>]*/i',
        ];
    }
}
