<?php

declare(strict_types=1);

namespace Slink\Image\Domain\ValueObject;

use InvalidArgumentException;
use Slink\Image\Domain\Enum\DimensionResolutionStrategy;
use Slink\Shared\Domain\ValueObject\AbstractCompoundValueObject;

final readonly class PartialImageDimensions extends AbstractCompoundValueObject {
  public function __construct(
    private ?int $width = null,
    private ?int $height = null
  ) {
    if ($width !== null && $width <= 0) {
      throw new InvalidArgumentException('Width must be a positive integer when provided');
    }
    if ($height !== null && $height <= 0) {
      throw new InvalidArgumentException('Height must be a positive integer when provided');
    }
    if ($width === null && $height === null) {
      throw new InvalidArgumentException('At least one dimension (width or height) must be specified');
    }
  }

  /**
   * @param array<string, mixed> $payload
   */
  public static function fromPayload(array $payload): static {
    return new self(
      width: isset($payload['width']) ? (int)$payload['width'] : null,
      height: isset($payload['height']) ? (int)$payload['height'] : null
    );
  }

  public function getHeight(): ?int {
    return $this->height;
  }

  public function getWidth(): ?int {
    return $this->width;
  }

  /**
   * @phpstan-assert-if-true !null $this->width
   * @phpstan-assert-if-true !null $this->height
   */
  public function hasBothDimensions(): bool {
    return $this->width !== null && $this->height !== null;
  }

  public function hasHeight(): bool {
    return $this->height !== null;
  }

  public function hasWidth(): bool {
    return $this->width !== null;
  }

  public function isPartial(): bool {
    return !$this->hasBothDimensions();
  }

  public function resolveToSquareCrop(): ImageDimensions {
    if ($this->hasBothDimensions()) {
      return new ImageDimensions($this->width, $this->height);
    }

    $size = (int)($this->width ?? $this->height);

    return new ImageDimensions($size, $size);
  }

  public function resolveWith(ImageDimensions $originalDimensions, DimensionResolutionStrategy $strategy): ImageDimensions {
    if ($this->hasBothDimensions()) {
      return new ImageDimensions($this->width, $this->height);
    }

    return match ($strategy) {
      DimensionResolutionStrategy::ASPECT_RATIO => ImageDimensions::createWithAspectRatio($this->width, $this->height, $originalDimensions),
      DimensionResolutionStrategy::ORIGINAL => $this->resolveWithOriginal($originalDimensions),
    };
  }

  /**
   * @return array<string, mixed>
   */
  public function toPayload(): array {
    return [
      'width' => $this->width,
      'height' => $this->height,
    ];
  }

  private function resolveWithOriginal(ImageDimensions $originalDimensions): ImageDimensions {
    return new ImageDimensions(
      $this->width ?? $originalDimensions->getWidth(),
      $this->height ?? $originalDimensions->getHeight()
    );
  }
}
