<?php

declare(strict_types=1);

namespace Slink\Image\Application\Service\Upload\Stage;

use Slink\Image\Application\Service\Upload\UploadContext;
use Slink\Image\Application\Service\Upload\UploadPhase;
use Slink\Image\Application\Service\Upload\UploadStageInterface;
use Slink\Image\Domain\Service\ImageConversionResolverInterface;
use Slink\Image\Domain\Service\ImageFileTransformerInterface;
use Slink\Image\Domain\ValueObject\ImageConversionOptions;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: UploadPhase::Transform->value - 1)]
final readonly class FormatConversionStage implements UploadStageInterface {
  public function __construct(
    private ImageConversionResolverInterface $conversionResolver,
    private ImageFileTransformerInterface $imageTransformer,
  ) {
  }

  public function process(UploadContext $context): UploadContext {
    $file = $context->file();
    $targetFormat = $this->conversionResolver->resolve($file);

    if ($targetFormat === null) {
      return $context;
    }

    $options = new ImageConversionOptions($targetFormat, stripMetadata: $context->stripExifMetadata());

    return $context->withFile($this->imageTransformer->convertToFormat($file, $options));
  }
}
