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
    private ?string $imageId = null,
    private ?string $userId = null,
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

  public function getImageId(): ?string {
    return $this->imageId;
  }

  public function getUserId(): ?string {
    return $this->userId;
  }

  public function withImageId(string $imageId): self {
    return new self(
      $this->width,
      $this->height,
      $this->quality,
      $this->crop,
      $this->format,
      $this->filter,
      $this->s,
      $imageId,
      $this->userId,
    );
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
      $this->imageId,
      $this->userId,
    );
  }

  public function withUserId(?string $userId): self {
    return new self(
      $this->width,
      $this->height,
      $this->quality,
      $this->crop,
      $this->format,
      $this->filter,
      $this->s,
      $this->imageId,
      $userId,
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
}
