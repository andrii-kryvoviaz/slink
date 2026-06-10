<?php

declare(strict_types=1);

namespace Slink\Image\Application\Service\Upload\Stage;

use Slink\Image\Application\Service\Upload\UploadContext;
use Slink\Image\Application\Service\Upload\UploadPhase;
use Slink\Image\Application\Service\Upload\UploadStageInterface;
use Slink\User\Application\Service\UserPreferencesService;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: UploadPhase::Prepare->value)]
final readonly class LoadPreferencesStage implements UploadStageInterface {
  public function __construct(
    private UserPreferencesService $preferencesService,
  ) {
  }

  public function process(UploadContext $context): UploadContext {
    return $context->withPreferences($this->preferencesService->getForUser($context->userId()));
  }
}
