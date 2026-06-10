<?php

declare(strict_types=1);

namespace Slink\Image\Application\Service\Upload\Stage;

use Slink\Image\Application\Service\Upload\UploadContext;
use Slink\Image\Application\Service\Upload\UploadPhase;
use Slink\Image\Application\Service\Upload\UploadStageInterface;
use Slink\Settings\Application\Service\SettingsService;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: UploadPhase::Resolve->value - 1)]
final readonly class ResolveLicenseStage implements UploadStageInterface {
  public function __construct(
    /** @var ConfigurationProviderInterface<SettingsService> */
    private ConfigurationProviderInterface $configurationProvider,
  ) {
  }

  public function process(UploadContext $context): UploadContext {
    if (!$this->configurationProvider->get('image.enableLicensing')) {
      return $context;
    }

    return $context->withLicense($context->preferences()->getDefaultLicense());
  }
}
