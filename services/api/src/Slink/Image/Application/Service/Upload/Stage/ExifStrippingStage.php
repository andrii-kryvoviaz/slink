<?php

declare(strict_types=1);

namespace Slink\Image\Application\Service\Upload\Stage;

use Slink\Image\Application\Service\Upload\UploadContext;
use Slink\Image\Application\Service\Upload\UploadPhase;
use Slink\Image\Application\Service\Upload\UploadStageInterface;
use Slink\Image\Domain\Service\ImageAnalyzerInterface;
use Slink\Image\Domain\Service\ImageFileTransformerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: UploadPhase::Transform->value - 2)]
final readonly class ExifStrippingStage implements UploadStageInterface {
  public function __construct(
    private ImageAnalyzerInterface $imageAnalyzer,
    private ImageFileTransformerInterface $imageTransformer,
  ) {
  }

  public function process(UploadContext $context): UploadContext {
    if (!$context->stripExifMetadata()) {
      return $context;
    }

    $file = $context->file();

    if (!$this->imageAnalyzer->supportsExifProfile($file->getMimeType())) {
      return $context;
    }

    $this->imageTransformer->stripExifMetadata($file->getPathname());

    return $context;
  }
}
