<?php

declare(strict_types=1);

namespace Slink\Image\Application\Service\Upload\Stage;

use Slink\Image\Application\Service\Upload\UploadContext;
use Slink\Image\Application\Service\Upload\UploadPhase;
use Slink\Image\Application\Service\Upload\UploadStageInterface;
use Slink\Image\Domain\Service\ImageAnalyzerInterface;
use Slink\Image\Domain\Service\ImageSanitizerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: UploadPhase::Transform->value)]
final readonly class SanitizationStage implements UploadStageInterface {
  public function __construct(
    private ImageAnalyzerInterface $imageAnalyzer,
    private ImageSanitizerInterface $sanitizer,
  ) {
  }

  public function process(UploadContext $context): UploadContext {
    $file = $context->file();

    if (!$this->imageAnalyzer->requiresSanitization($file->getMimeType())) {
      return $context;
    }

    return $context->withFile($this->sanitizer->sanitizeFile($file));
  }
}
