<?php

declare(strict_types=1);

namespace Slink\Image\Application\Service\Sanitizer\Rule;

final class CssImportRemovalRule extends AbstractRegexSanitizationRule {
    
    public function getName(): string {
        return 'css_import_removal';
    }
    
    public function getPriority(): int {
        return 35;
    }
    
    protected function getPatterns(): array {
        return [
            '/@import\s+url\s*\([^)]*\)\s*;?/i',
            '/@import\s+["\'][^"\']*["\'];?/i',
            '/@import\s+[^;]+;/i',
            '/@import[^;}]*/i',
        ];
    }
}
