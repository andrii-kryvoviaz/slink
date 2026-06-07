<?php

declare(strict_types=1);

namespace Slink\Image\Domain\ValueObject;

use Slink\Shared\Domain\ValueObject\AbstractCompoundValueObject;
use Slink\Shared\Domain\ValueObject\ImageOptions;

final readonly class ImageTransformationRequest extends AbstractCompoundValueObject {
  public function __construct(
    private ?ImageDimensions        $targetDimensions = null,
    private ?PartialImageDimensions $partialDimensions = null,
    private bool                    $crop = false,
    private ?int                    $quality = null,
    private bool                    $upscale = false,
    private ?string                 $filter = null
  ) {
  }

  public static function fromImageOptions(ImageOptions $options): self {
    $width = $options->getWidth();
    $height = $options->getHeight();

    $targetDimensions = ($width !== null && $height !== null)
      ? new ImageDimensions($width, $height)
      : null;

    $partialDimensions = ($width !== null || $height !== null)
      ? new PartialImageDimensions($width, $height)
      : null;

    return new self(
      targetDimensions: $targetDimensions,
      partialDimensions: $partialDimensions,
      crop: $options->isCropped(),
      quality: $options->getQuality(),
      filter: $options->getFilter()
    );
  }

  /**
   * @param array<string, mixed> $payload
   * @return static
   */
  public static function fromPayload(array $payload): static {
    $targetDimensions = null;
    if ($payload['targetDimensions'] !== null) {
      $targetDimensions = ImageDimensions::fromPayload($payload['targetDimensions']);
    }

    $partialDimensions = null;
    if ($payload['partialDimensions'] !== null) {
      $partialDimensions = PartialImageDimensions::fromPayload($payload['partialDimensions']);
    }

    return new self(
      targetDimensions: $targetDimensions,
      partialDimensions: $partialDimensions,
      crop: (bool)($payload['crop'] ?? false),
      quality: isset($payload['quality']) ? (int)$payload['quality'] : null,
      upscale: (bool)($payload['upscale'] ?? false),
      filter: isset($payload['filter']) ? (string)$payload['filter'] : null
    );
  }

  public function upscale(): bool {
    return $this->upscale;
  }

  public function getFilter(): ?string {
    return $this->filter;
  }

  public function getPartialDimensions(): ?PartialImageDimensions {
    return $this->partialDimensions;
  }

  public function getQuality(): ?int {
    return $this->quality;
  }

  public function getTargetDimensions(): ?ImageDimensions {
    return $this->targetDimensions;
  }

  public function hasPartialDimensions(): bool {
    return $this->partialDimensions !== null;
  }

  public function shouldCrop(): bool {
    return $this->crop;
  }

  /**
   * @return array<string, mixed>
   */
  public function toPayload(): array {
    return [
      'targetDimensions' => $this->targetDimensions?->toPayload(),
      'partialDimensions' => $this->partialDimensions?->toPayload(),
      'crop' => $this->crop,
      'quality' => $this->quality,
      'upscale' => $this->upscale,
      'filter' => $this->filter,
    ];
  }
}
