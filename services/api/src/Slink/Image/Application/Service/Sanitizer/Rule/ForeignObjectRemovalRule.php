<?php

declare(strict_types=1);

namespace Slink\Image\Application\Service\Sanitizer\Rule;

final class ForeignObjectRemovalRule extends AbstractRegexSanitizationRule {
    
    public function getName(): string {
        return 'foreign_object_removal';
    }
    
    public function getPriority(): int {
        return 50;
    }
    
    protected function getPatterns(): array {
        return [
            '/<foreignObject[^>]*>.*?<\/foreignObject>/is',
            '/<foreignObject[^>]*\/>/is',
        ];
    }
}
