<?php

declare(strict_types=1);

namespace Slink\Image\Application\Service;

use enshrined\svgSanitize\Sanitizer;
use Slink\Image\Application\Service\Sanitizer\RuleBasedSanitizer;
use Slink\Image\Application\Service\Sanitizer\Rule\DataUriRemovalRule;
use Slink\Image\Application\Service\Sanitizer\Rule\DoctypeRemovalRule;
use Slink\Image\Application\Service\Sanitizer\Rule\EventHandlerRemovalRule;
use Slink\Image\Application\Service\Sanitizer\Rule\ExternalReferenceRemovalRule;
use Slink\Image\Application\Service\Sanitizer\Rule\ForeignObjectRemovalRule;
use Slink\Image\Application\Service\Sanitizer\Rule\JavaScriptUrlRemovalRule;
use Slink\Image\Application\Service\Sanitizer\Rule\ScriptRemovalRule;
use Slink\Image\Application\Service\Sanitizer\Rule\CssExpressionRemovalRule;
use Slink\Image\Application\Service\Sanitizer\Rule\CssImportRemovalRule;
use Slink\Image\Domain\Service\ImageSanitizerInterface;
use Symfony\Component\HttpFoundation\File\File;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

#[AsAlias(ImageSanitizerInterface::class)]
final class SvgSanitizer implements ImageSanitizerInterface {
    private readonly Sanitizer $baseSanitizer;
    private readonly RuleBasedSanitizer $enhancedSanitizer;
    
    public function __construct() {
        $this->baseSanitizer = new Sanitizer();
        $this->configureSanitizer();
        
        $this->enhancedSanitizer = new RuleBasedSanitizer(
            new DoctypeRemovalRule(),
            new ScriptRemovalRule(),
            new EventHandlerRemovalRule(),
            new JavaScriptUrlRemovalRule(),
            new DataUriRemovalRule(),
            new ExternalReferenceRemovalRule(),
            new ForeignObjectRemovalRule(),
            new CssExpressionRemovalRule(),
            new CssImportRemovalRule(),
        );
    }
    
    public function sanitize(string $svgContent): string {
        $sanitizedContent = $this->baseSanitizer->sanitize($svgContent);
        
        if ($sanitizedContent === false) {
            $sanitizedContent = $this->enhancedSanitizer->sanitize($svgContent);
        } else {
            $sanitizedContent = $this->enhancedSanitizer->sanitize($sanitizedContent);
        }
        
        return $sanitizedContent;
    }
    
    public function sanitizeFile(File $file): File {
        if (!$file->isReadable()) {
            throw new RuntimeException('SVG file is not readable');
        }
        
        $content = file_get_contents($file->getPathname());
        if ($content === false) {
            throw new RuntimeException('Failed to read SVG file');
        }
        
        $sanitizedContent = $this->sanitize($content);
        
        if (file_put_contents($file->getPathname(), $sanitizedContent) === false) {
            throw new RuntimeException('Failed to write sanitized SVG content');
        }
        
        return $file;
    }
    
    public function requiresSanitization(?string $mimeType): bool {
        return $mimeType === 'image/svg+xml' || $mimeType === 'image/svg';
    }
    
    private function configureSanitizer(): void {
        $this->baseSanitizer->removeRemoteReferences(true);
        $this->baseSanitizer->minify(true);
    }
}
