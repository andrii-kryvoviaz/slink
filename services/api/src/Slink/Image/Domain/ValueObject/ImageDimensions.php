<?php

declare(strict_types=1);

namespace Slink\Image\Domain\ValueObject;

use InvalidArgumentException;
use Slink\Shared\Domain\ValueObject\AbstractCompoundValueObject;

final readonly class ImageDimensions extends AbstractCompoundValueObject {
  public function __construct(
    private int $width,
    private int $height
  ) {
    if ($width <= 0 || $height <= 0) {
      throw new InvalidArgumentException('Width and height must be positive integers');
    }
  }

  public static function createWithAspectRatio(
    ?int             $width,
    ?int             $height,
    ?ImageDimensions $originalDimensions = null
  ): self {
    if ($width === null && $height === null) {
      throw new InvalidArgumentException('At least one dimension (width or height) must be specified');
    }

    if ($width !== null && $height !== null) {
      return new self($width, $height);
    }

    if ($originalDimensions === null) {
      throw new InvalidArgumentException('Original dimensions required for aspect ratio calculation when only one dimension is specified');
    }

    $aspectRatio = $originalDimensions->getAspectRatio();

    if ($width === null) {
      $calculatedWidth = (int)round($height * $aspectRatio);
      return new self($calculatedWidth, $height);
    } else {
      $calculatedHeight = (int)round($width / $aspectRatio);
      return new self($width, $calculatedHeight);
    }
  }

  /**
   * @param array<string, mixed> $payload
   * @return static
   */
  public static function fromPayload(array $payload): static {
    return new self(
      (int)$payload['width'],
      (int)$payload['height']
    );
  }

  public function fitsWithin(self $bounds): bool {
    return $this->width <= $bounds->width && $this->height <= $bounds->height;
  }

  public function getAspectRatio(): float {
    return $this->width / $this->height;
  }

  public function getHeight(): int {
    return $this->height;
  }

  public function getWidth(): int {
    return $this->width;
  }

  public function isLandscape(): bool {
    return $this->width > $this->height;
  }

  public function isPortrait(): bool {
    return $this->width < $this->height;
  }

  public function isSquare(): bool {
    return $this->width === $this->height;
  }

  public function scale(float $scale): self {
    return new self(
      (int)round($this->width * $scale),
      (int)round($this->height * $scale)
    );
  }

  public function scaleToFitWithin(self $bounds, bool $allowEnlarge = false): self {
    if (!$allowEnlarge && $this->fitsWithin($bounds)) {
      return $this;
    }

    $widthRatio = $bounds->width / $this->width;
    $heightRatio = $bounds->height / $this->height;
    $scale = min($widthRatio, $heightRatio);

    return $this->scale($scale);
  }

  /**
   * @return array<string, int>
   */
  public function toPayload(): array {
    return [
      'width' => $this->width,
      'height' => $this->height,
    ];
  }
}
