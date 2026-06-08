<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\Service;

use Jcupitt\Vips\Image as VipsImage;
use Slink\Image\Domain\Service\ImageSource;

final class VipsImageSource {
  private ?string $bytes = null;

  public function __construct(private readonly ImageSource $source) {
  }

  /**
   * @param array<string, mixed> $options
   */
  public function load(array $options = ['access' => 'sequential']): VipsImage {
    if ($this->source->hasLocalPath()) {
      return VipsImage::newFromFile($this->source->getLocalPath(), $options);
    }

    return VipsImage::newFromBuffer($this->bytes(), '', $options);
  }

  /**
   * @param array<string, mixed> $options
   */
  public function loadThumbnail(int $targetWidth, array $options, bool $allPages = false): VipsImage {
    if ($this->source->hasLocalPath()) {
      $path = $this->source->getLocalPath() . ($allPages ? '[n=-1]' : '');

      return VipsImage::thumbnail($path, $targetWidth, $options);
    }

    $bufferOptions = $allPages
      ? $options + ['option_string' => '[n=-1]']
      : $options;

    return VipsImage::thumbnail_buffer($this->bytes(), $targetWidth, $bufferOptions);
  }

  private function bytes(): string {
    $this->bytes ??= $this->source->readBytes();

    return $this->bytes;
  }
}
