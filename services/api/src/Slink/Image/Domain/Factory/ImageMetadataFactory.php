<?php

declare(strict_types=1);

namespace Slink\Image\Domain\Factory;

use Slink\Image\Domain\Service\ImageAnalyzerInterface;
use Slink\Image\Domain\Service\ImageHashCalculatorInterface;
use Slink\Image\Domain\ValueObject\ImageFile;
use Slink\Image\Domain\ValueObject\ImageMetadata;
use Symfony\Component\HttpFoundation\File\File;

final readonly class ImageMetadataFactory {
  public function __construct(
    private ImageAnalyzerInterface       $imageAnalyzer,
    private ImageHashCalculatorInterface $hashCalculator
  ) {
  }

  public function createFromFile(File $file): ImageMetadata {
    $imageFile = ImageFile::fromSymfonyFile($file);
    return $this->createFromImageFile($imageFile);
  }

  public function createFromImageFile(ImageFile $imageFile): ImageMetadata {
    $file = new File($imageFile->getPathname());
    $analysisData = $this->imageAnalyzer->analyze($file);
    $hash = $this->hashCalculator->calculateFromImageFile($imageFile);

    return new ImageMetadata(
      $analysisData['size'],
      $analysisData['mimeType'],
      $analysisData['width'],
      $analysisData['height'],
      $hash
    );
  }

  /**
   * @param array<string, mixed> $analysisData
   */
  public function createFromAnalysisData(array $analysisData, string $fileContent): ImageMetadata {
    $hash = $this->hashCalculator->calculateFromContent($fileContent);

    return new ImageMetadata(
      $analysisData['size'],
      $analysisData['mimeType'],
      $analysisData['width'],
      $analysisData['height'],
      $hash
    );
  }
}