<?php

declare(strict_types=1);

namespace Slink\Image\Application\Service\Sanitizer\Rule;

final class ExternalReferenceRemovalRule extends AbstractRegexSanitizationRule {
    
    public function getName(): string {
        return 'external_reference_removal';
    }
    
    public function getPriority(): int {
        return 40;
    }
    
    protected function getPatterns(): array {
        return [
            '/\s+href\s*=\s*["\']https?:\/\/[^"\']*["\']/i',
            '/\s+xlink:href\s*=\s*["\']https?:\/\/[^"\']*["\']/i',
            '/\s+src\s*=\s*["\']https?:\/\/[^"\']*["\']/i',
            '/\s+href\s*=\s*["\']ftp:\/\/[^"\']*["\']/i',
            '/\s+href\s*=\s*["\']file:\/\/\/[^"\']*["\']/i',
            '/href\s*=\s*["\']?https?:\/\/[^"\'\s>]*/i',
            '/xlink:href\s*=\s*["\']?https?:\/\/[^"\'\s>]*/i',
        ];
    }
}
