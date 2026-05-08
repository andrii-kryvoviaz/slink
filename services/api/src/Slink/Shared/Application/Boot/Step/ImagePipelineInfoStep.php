<?php

declare(strict_types=1);

namespace Slink\Shared\Application\Boot\Step;

use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Application\Boot\BootCategory;
use Slink\Shared\Application\Boot\BootContext;
use Slink\Shared\Application\Boot\BootResult;
use Slink\Shared\Application\Boot\BootStepInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: 80)]
final readonly class ImagePipelineInfoStep implements BootStepInterface {
  public function __construct(
    private ConfigurationProviderInterface $config,
  ) {}

  public function label(): string {
    return 'image pipeline';
  }

  public function category(): BootCategory {
    return BootCategory::Config;
  }

  public function run(BootContext $context): BootResult {
    $compression = (int) $this->config->get('image.compressionQuality');
    $forceConvert = (bool) $this->config->get('image.forceFormatConversion');

    $rows = [
      ['Strip EXIF Metadata', $this->boolLabel('image.stripExifMetadata')],
      ['Image Compression Quality', sprintf('%d%%', $compression)],
      ['Force Format Conversion', $forceConvert ? 'Enabled' : 'Disabled'],
    ];

    if ($forceConvert) {
      $rows[] = ['Target Image Format', strtolower((string) $this->config->get('image.targetFormat'))];
      $rows[] = ['Convert Animated Images', $this->boolLabel('image.convertAnimatedImages')];
    }

    $rows[] = ['Image Deduplication', $this->boolLabel('image.enableDeduplication')];
    $rows[] = ['Image Licensing', $this->boolLabel('image.enableLicensing')];
    $rows[] = ['Restrict to Public Images', $this->boolLabel('image.allowOnlyPublicImages')];

    return BootResult::settings($rows);
  }

  private function boolLabel(string $key): string {
    return ((bool) $this->config->get($key)) ? 'Enabled' : 'Disabled';
  }
}
