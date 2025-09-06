<?php

declare(strict_types=1);

namespace Slink\Image\Application\Service\Sanitizer\Rule;

abstract class AbstractRegexSanitizationRule implements SanitizationRuleInterface {
    
    public function sanitize(string $svgContent): string {
        $patterns = $this->getPatterns();
        
        foreach ($patterns as $pattern) {
            $svgContent = preg_replace($pattern, '', $svgContent) ?? $svgContent;
        }
        
        return $svgContent;
    }
    
    /**
     * @return string[] Array of regex patterns
     */
    abstract protected function getPatterns(): array;
    
    public function getPriority(): int {
        return 100;
    }
}
