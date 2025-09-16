<?php

declare(strict_types=1);

namespace Slink\Image\Domain\Exception;

use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Shared\Domain\Exception\SpecificationException;

final class DuplicateImageException extends SpecificationException {
  private string $imageId;
  private string $uploadedAt;

  public function __construct(
    private readonly ImageView $existingImage
  ) {
    $this->imageId = $existingImage->getUuid();
    $this->uploadedAt = $existingImage->getAttributes()->getCreatedAt()->format('c'); // ISO 8601 format
    
    $message = sprintf(
      'Image already exists: %s',
      $this->imageId
    );
    
    parent::__construct($message);
  }

  public function getExistingImage(): ImageView {
    return $this->existingImage;
  }

  public function getImageId(): string {
    return $this->imageId;
  }

  public function getUploadedAt(): string {
    return $this->uploadedAt;
  }

  public function getProperty(): string {
    return 'duplicate_image';
  }

  /**
   * Additional data for frontend custom component rendering
   * @return array<string, mixed>
   */
  public function toPayload(): array {
    return [
      'imageId' => $this->imageId,
      'uploadedAt' => $this->uploadedAt,
      'existingImageUrl' => "/info/{$this->imageId}",
    ];
  }
}