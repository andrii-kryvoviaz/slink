<?php

declare(strict_types=1);

namespace Slink\Image\Application\Command\UpdateImage;

use Slink\Image\Domain\Repository\ImageStoreRepositoryInterface;
use Slink\Settings\Application\Service\SettingsService;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\User\Infrastructure\Auth\JwtUser;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final readonly class UpdateImageHandler implements CommandHandlerInterface {

  /**
   * @param ConfigurationProviderInterface<SettingsService> $configurationProvider
   * @param ImageStoreRepositoryInterface $imageRepository
   */
  public function __construct(
    private ConfigurationProviderInterface $configurationProvider,
    private ImageStoreRepositoryInterface  $imageRepository,
  ) {
  }

  /**
   * @throws NotFoundException
   */
  public function __invoke(
    UpdateImageCommand $command,
    ?JwtUser           $user,
    string             $id
  ): void {
    $image = $this->imageRepository->get(ID::fromString($id));
    $userId = ID::fromString($user?->getIdentifier() ?? '');

    if (!$image->aggregateRootVersion()) {
      throw new NotFoundException();
    }

    if (!$user || !$image->getUserId() || !$image->getUserId()->equals($userId)) {
      throw new AccessDeniedException();
    }

    $isPublic = $command->getIsPublic();
    if ($this->configurationProvider->get('image.allowOnlyPublicImages')) {
      $isPublic = true;
    }

    $attributes = clone $image->getAttributes()
      ->withDescription($command->getDescription())
      ->withIsPublic($isPublic);

    $image->updateAttributes($attributes);

    if ($this->configurationProvider->get('image.enableLicensing') && $command->getLicense() !== null) {
      $image->updateLicense($command->getLicense());
    }

    $this->imageRepository->store($image);
  }
}