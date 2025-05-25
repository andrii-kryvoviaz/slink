<?php

declare(strict_types=1);

namespace Slink\Image\Application\Query\GetImageContent;

use Slink\Shared\Application\Query\QueryInterface;
use Slink\Shared\Domain\ValueObject\ImageOptions;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;

final readonly class GetImageContentQuery implements QueryInterface {
  use EnvelopedMessage;
  
  /**
   * @param int|null $width
   * @param int|null $height
   * @param int|null $quality
   * @param bool $crop
   */
  public function __construct(
    private ?int $width = null,
    private ?int $height = null,
    private ?int $quality = null,
    private bool $crop = false
  ) {
  }
  
  /**
   * @return int|null
   */
  public function getWidth(): ?int {
    return $this->width;
  }
  
  /**
   * @return int|null
   */
  public function getHeight(): ?int {
    return $this->height;
  }
  
  /**
   * @return int|null
   */
  public function getQuality(): ?int {
    return $this->quality;
  }
  
  /**
   * @return bool
   */
  public function isCropped(): bool {
    return $this->crop;
  }
  
  /**
   * @return array<string, mixed>
   */
  public function getTransformParams(): array {
    return [
      'width' => $this->width,
      'height' => $this->height,
      'quality' => $this->quality,
      'crop' => $this->crop,
    ];
  }
}