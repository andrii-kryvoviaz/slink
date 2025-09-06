<?php

declare(strict_types=1);

namespace Slink\Image\Application\Service\Sanitizer\Rule;

final class JavaScriptUrlRemovalRule extends AbstractRegexSanitizationRule {
    
    public function getName(): string {
        return 'javascript_url_removal';
    }
    
    public function getPriority(): int {
        return 30;
    }
    
    protected function getPatterns(): array {
        return [
            '/\s+href\s*=\s*["\']javascript:[^"\']*["\']/i',
            '/\s+xlink:href\s*=\s*["\']javascript:[^"\']*["\']/i',
            '/\s+src\s*=\s*["\']javascript:[^"\']*["\']/i',
            '/url\(\s*["\']?javascript:[^)]*\)/i',
        ];
    }
}
