<?php

declare(strict_types=1);

namespace Slink\Image\Application\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use function in_array;

final readonly class ImageCapabilityChecker {
  /**
   * @param array<string> $resizableMimeTypes
   * @param array<string> $stripExifMimeTypes
   * @param array<string> $enforceConversionMimeTypes
   * @param array<string> $sanitizationRequiredMimeTypes
   */
  public function __construct(
    #[Autowire(param: 'supports_resize')]
    private array $resizableMimeTypes,
    #[Autowire(param: 'supports_strip_exif')]
    private array $stripExifMimeTypes,
    #[Autowire(param: 'enforce_conversion')]
    private array $enforceConversionMimeTypes,
    #[Autowire(param: 'requires_sanitization')]
    private array $sanitizationRequiredMimeTypes = ['image/svg+xml', 'image/svg']
  ) {
  }

  public function isConversionRequired(?string $mimeType): bool {
    return in_array($mimeType, $this->enforceConversionMimeTypes, true);
  }

  public function supportsExifProfile(?string $mimeType): bool {
    return in_array($mimeType, $this->stripExifMimeTypes, true);
  }

  public function supportsResize(?string $mimeType): bool {
    return in_array($mimeType, $this->resizableMimeTypes, true);
  }
  
  public function requiresSanitization(?string $mimeType): bool {
    return in_array($mimeType, $this->sanitizationRequiredMimeTypes, true);
  }
}
