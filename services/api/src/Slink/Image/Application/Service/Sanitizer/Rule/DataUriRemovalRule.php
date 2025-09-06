<?php

declare(strict_types=1);

namespace Slink\Image\Application\Service\Sanitizer\Rule;

final class DataUriRemovalRule extends AbstractRegexSanitizationRule {
    
    public function getName(): string {
        return 'data_uri_removal';
    }
    
    public function getPriority(): int {
        return 35;
    }
    
    protected function getPatterns(): array {
        return [
            '/\s+href\s*=\s*["\']data:text\/html[^"\']*["\']/i',
            '/\s+xlink:href\s*=\s*["\']data:text\/html[^"\']*["\']/i',
            '/\s+href\s*=\s*["\']data:image\/svg\+xml[^"\']*["\']/i',
            '/\s+xlink:href\s*=\s*["\']data:image\/svg\+xml[^"\']*["\']/i',
            '/url\(\s*["\']?data:text\/html[^)]*\)/i',
        ];
    }
}
