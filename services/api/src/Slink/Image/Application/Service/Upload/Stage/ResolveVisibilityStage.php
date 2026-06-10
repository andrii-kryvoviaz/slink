<?php

declare(strict_types=1);

namespace Slink\Image\Application\Service\Upload\Stage;

use Slink\Image\Application\Service\Upload\UploadContext;
use Slink\Image\Application\Service\Upload\UploadPhase;
use Slink\Image\Application\Service\Upload\UploadStageInterface;
use Slink\Settings\Application\Service\SettingsService;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: UploadPhase::Resolve->value)]
final readonly class ResolveVisibilityStage implements UploadStageInterface {
  public function __construct(
    /** @var ConfigurationProviderInterface<SettingsService> */
    private ConfigurationProviderInterface $configurationProvider,
  ) {
  }

  public function process(UploadContext $context): UploadContext {
    $isPublic = $context->preferences()->resolveVisibility($context->requestedPublic());

    if ($this->configurationProvider->get('image.allowOnlyPublicImages') || $context->userId() === null) {
      $isPublic = true;
    }

    return $context->withVisibility($isPublic);
  }
}
