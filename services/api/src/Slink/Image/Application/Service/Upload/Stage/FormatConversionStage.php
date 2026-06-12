<?php

declare(strict_types=1);

namespace Slink\Image\Application\Service\Upload\Stage;

use Slink\Image\Application\Service\Upload\UploadContext;
use Slink\Image\Application\Service\Upload\UploadPhase;
use Slink\Image\Application\Service\Upload\UploadStageInterface;
use Slink\Image\Domain\Service\ImageConversionResolverInterface;
use Slink\Image\Domain\Service\ImageFileTransformerInterface;
use Slink\Settings\Application\Service\SettingsService;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: UploadPhase::Transform->value - 1)]
final readonly class FormatConversionStage implements UploadStageInterface {
  public function __construct(
    private ImageConversionResolverInterface $conversionResolver,
    private ImageFileTransformerInterface $imageTransformer,
    /** @var ConfigurationProviderInterface<SettingsService> */
    private ConfigurationProviderInterface $configurationProvider,
  ) {
  }

  public function process(UploadContext $context): UploadContext {
    $file = $context->file();
    $targetFormat = $this->conversionResolver->resolve($file);

    if ($targetFormat === null) {
      return $context;
    }

    $serverStrip = (bool) $this->configurationProvider->get('image.stripExifMetadata');
    $strip = $context->preferences()->getExifMetadataPreference()->resolveStripExif($serverStrip);

    return $context->withFile($this->imageTransformer->convertToFormat($file, $targetFormat, strip: $strip));
  }
}
