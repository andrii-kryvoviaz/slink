<?php

declare(strict_types=1);

namespace Slink\Image\Domain\Specification;

use Slink\Image\Domain\Exception\DuplicateImageException;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Image\Domain\Service\ImageHashCalculatorInterface;
use Slink\Image\Domain\ValueObject\ImageFile;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Domain\ValueObject\ID;

final readonly class ImageDuplicateSpecification implements ImageDuplicateSpecificationInterface {
  public function __construct(
    private ImageRepositoryInterface       $imageRepository,
    private ImageHashCalculatorInterface   $hashCalculator,
    private ConfigurationProviderInterface $configurationProvider
  ) {
  }

  public function ensureNoDuplicate(ImageFile $imageFile, ?ID $userId = null): void {
    $sha1Hash = $this->hashCalculator->calculateFromImageFile($imageFile);

    $deduplicationEnabled = $this->configurationProvider->get('image.enableDeduplication') ?? true;

    if (!$deduplicationEnabled) {
      return;
    }

    $existingImage = $this->imageRepository->findBySha1Hash($sha1Hash, $userId);

    if ($existingImage !== null) {
      throw new DuplicateImageException($existingImage);
    }
  }
}