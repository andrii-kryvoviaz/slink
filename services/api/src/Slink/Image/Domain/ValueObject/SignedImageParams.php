<?php

declare(strict_types=1);

namespace Slink\Image\Domain\ValueObject;

use Slink\Shared\Domain\ValueObject\AbstractCompoundValueObject;

final readonly class SignedImageParams extends AbstractCompoundValueObject {
  public function __construct(
    private string $imageId,
    private ?int $width,
    private ?int $height,
    private bool $crop,
    private string $signature
  ) {
  }

  public static function create(
    string $imageId,
    ?int $width,
    ?int $height,
    bool $crop,
    string $signature
  ): self {
    return new self($imageId, $width, $height, $crop, $signature);
  }

  public function getImageId(): string {
    return $this->imageId;
  }

  public function getWidth(): ?int {
    return $this->width;
  }

  public function getHeight(): ?int {
    return $this->height;
  }

  public function isCropped(): bool {
    return $this->crop;
  }

  public function getSignature(): string {
    return $this->signature;
  }

  public function toPayload(): array {
    return [
      'imageId' => $this->imageId,
      'width' => $this->width,
      'height' => $this->height,
      'crop' => $this->crop,
      'signature' => $this->signature,
    ];
  }

  public static function fromPayload(array $payload): static {
    return new self(
      $payload['imageId'],
      $payload['width'] ?? null,
      $payload['height'] ?? null,
      $payload['crop'] ?? false,
      $payload['signature']
    );
  }
}
