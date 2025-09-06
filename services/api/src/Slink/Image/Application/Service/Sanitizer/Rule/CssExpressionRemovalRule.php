<?php

declare(strict_types=1);

namespace Slink\Image\Application\Service\Sanitizer\Rule;

final class CssExpressionRemovalRule extends AbstractRegexSanitizationRule {
    
    public function getName(): string {
        return 'css_expression_removal';
    }
    
    public function getPriority(): int {
        return 25;
    }
    
    protected function getPatterns(): array {
        return [
            '/expression\s*\([^)]*\)/i',
            '/behavior\s*:\s*url\s*\([^)]*\)/i',
            '/javascript\s*:\s*[^;"}]*/i',
            '/-moz-binding\s*:\s*url\s*\([^)]*\)/i',
            '/url\s*\(\s*["\']?data:[^"\']*["\']?\s*\)/i',
            '/content\s*:\s*url\s*\([^)]*\)/i',
        ];
    }
}
