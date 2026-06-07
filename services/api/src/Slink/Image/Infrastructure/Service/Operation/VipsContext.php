<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\Service\Operation;

use Jcupitt\Vips\Image as VipsImage;
use Slink\Image\Infrastructure\Service\VipsImageSource;

#[\Symfony\Component\DependencyInjection\Attribute\Exclude]
final class VipsContext {
  private ?VipsImage $image = null;

  private ?int $resizeWidth = null;

  /** @var array<string, mixed>|null */
  private ?array $resizeOptions = null;

  private function __construct(
    private readonly ?VipsImageSource $source
  ) {
  }

  public static function forSource(VipsImageSource $source): self {
    return new self($source);
  }

  public static function forFrame(VipsImage $frame): self {
    $context = new self(null);
    $context->image = $frame;

    return $context;
  }

  /**
   * @param array<string, mixed> $options
   */
  public function resize(int $width, array $options): void {
    if ($this->resizeWidth !== null) {
      return;
    }

    $this->resizeWidth = $width;
    $this->resizeOptions = $options;

    if ($this->image !== null) {
      $this->image = $this->image->thumbnail_image($width, $options);
    }
  }

  /**
   * @param callable(VipsImage): VipsImage $transform
   */
  public function transform(callable $transform): void {
    $this->image = $transform($this->image());
  }

  public function image(): VipsImage {
    if ($this->image !== null) {
      return $this->image;
    }

    $this->image = $this->load();

    return $this->image;
  }

  public function result(): VipsImage {
    return $this->image();
  }

  private function load(): VipsImage {
    if ($this->resizeWidth !== null && $this->resizeOptions !== null) {
      return $this->source()->loadThumbnail($this->resizeWidth, $this->resizeOptions);
    }

    return $this->source()->load(['access' => 'sequential']);
  }

  private function source(): VipsImageSource {
    \assert($this->source !== null);

    return $this->source;
  }
}
