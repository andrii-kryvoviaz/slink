<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Image\ChunkedUpload\Request;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class InitUploadRequest {
  /**
   * @param string $fileName
   * @param int $totalSize
   * @param string $mimeType
   * @param bool $isPublic
   * @param string $description
   * @param array<string> $tagIds
   * @param array<string> $collectionIds
   */
  public function __construct(
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public string $fileName,

    #[Assert\Positive]
    public int $totalSize,

    #[Assert\NotBlank]
    public string $mimeType,

    public bool $isPublic = false,

    #[Assert\Length(max: 255)]
    public string $description = '',

    #[Assert\All([
      new Assert\Uuid(message: 'Invalid tag ID format'),
    ])]
    public array $tagIds = [],

    #[Assert\All([
      new Assert\Uuid(message: 'Invalid collection ID format'),
    ])]
    public array $collectionIds = [],
  ) {
  }
}
