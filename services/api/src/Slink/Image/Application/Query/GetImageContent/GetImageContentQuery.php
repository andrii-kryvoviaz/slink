<?php

declare(strict_types=1);

namespace Slink\Image\Application\Query\GetImageContent;

use Slink\Shared\Application\Query\QueryInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;

final readonly class GetImageContentQuery implements QueryInterface {
  use EnvelopedMessage;

  public function __construct(
    private ?int $width = null,
    private ?int $height = null,
    private ?int $quality = null,
    private bool $crop = false,
    private ?string $format = null,
    private ?string $filter = null,
    private ?string $s = null,
    private ?string $collection = null,
    private ?string $cs = null,
  ) {
  }

  public function getWidth(): ?int {
    return $this->width;
  }

  public function getHeight(): ?int {
    return $this->height;
  }

  public function getQuality(): ?int {
    return $this->quality;
  }

  public function isCropped(): bool {
    return $this->crop;
  }

  public function getFormat(): ?string {
    return $this->format;
  }

  public function getFilter(): ?string {
    return $this->filter;
  }

  public function getTransformSignature(): ?string {
    return $this->s;
  }

  public function getScopeCollectionId(): ?string {
    return $this->collection;
  }

  public function getScopeSignature(): ?string {
    return $this->cs;
  }

  public function hasTransformParams(): bool {
    if ($this->width !== null) {
      return true;
    }

    if ($this->height !== null) {
      return true;
    }

    if ($this->crop) {
      return true;
    }

    if ($this->filter !== null) {
      return true;
    }

    return false;
  }

  public function withFormat(?string $format): self {
    return new self(
      $this->width,
      $this->height,
      $this->quality,
      $this->crop,
      $format,
      $this->filter,
      $this->s,
      $this->collection,
      $this->cs,
    );
  }

  public function withoutTransformParams(): self {
    return new self(
      null,
      null,
      null,
      false,
      $this->format,
      null,
      null,
      $this->collection,
      $this->cs,
    );
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
      'filter' => $this->filter,
    ];
  }

  /**
   * @return array<string, int|bool|string>
   */
  public function getSignedTransformParams(): array {
    return array_filter([
      'width' => $this->width,
      'height' => $this->height,
      'crop' => $this->crop,
      'filter' => $this->filter,
    ], fn($value) => $value !== null && $value !== false);
  }
}
