<?php

declare(strict_types=1);

namespace Slink\Settings\Domain\Enum;

enum BrandingLogoFormat: string {
  case Svg = 'image/svg+xml';
  case Png = 'image/png';

  public static function isSvgMimeType(string $mimeType): bool {
    return self::tryFrom($mimeType) === self::Svg;
  }

  public static function fromUploadedMimeType(?string $mimeType): self {
    if ($mimeType === self::Svg->value) {
      return self::Svg;
    }

    return self::Png;
  }

  public function fileName(): string {
    return match ($this) {
      self::Svg => 'branding-logo.svg',
      self::Png => 'branding-logo.png',
    };
  }

  public function mimeType(): string {
    return $this->value;
  }

  public function requiresConversion(): bool {
    return $this === self::Png;
  }
}
