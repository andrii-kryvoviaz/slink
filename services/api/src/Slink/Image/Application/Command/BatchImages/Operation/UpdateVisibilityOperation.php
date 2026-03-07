<?php

declare(strict_types=1);

namespace Slink\Image\Application\Command\BatchImages\Operation;

use Slink\Image\Application\Command\BatchImages\BatchImagesCommand;
use Slink\Image\Domain\Image;
use Slink\Settings\Application\Service\SettingsService;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('batch_image.operation')]
final readonly class UpdateVisibilityOperation implements BatchImageOperationInterface {
  /**
   * @param ConfigurationProviderInterface<SettingsService> $configurationProvider
   */
  public function __construct(
    private ConfigurationProviderInterface $configurationProvider,
  ) {}

  public function supports(BatchImagesCommand $command): bool {
    return $command->isPublic() !== null;
  }

  public function apply(Image $image, BatchImagesCommand $command, ID $userId): void {
    $isPublic = $command->isPublic();

    if ($isPublic === null) {
      return;
    }

    if ($this->configurationProvider->get('image.allowOnlyPublicImages')) {
      $isPublic = true;
    }

    $attributes = clone $image->getAttributes()
      ->withIsPublic($isPublic);

    $image->updateAttributes($attributes);
  }

}
