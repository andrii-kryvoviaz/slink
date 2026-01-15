<?php

declare(strict_types=1);

namespace Slink\Image\Application\Service;

use Slink\Image\Domain\Enum\License;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Image\Domain\Repository\ImageStoreRepositoryInterface;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Domain\ValueObject\ID;

final readonly class LicenseSyncService implements LicenseSyncServiceInterface {
  public function __construct(
    private ImageRepositoryInterface $imageRepository,
    private ImageStoreRepositoryInterface $imageStore,
    private ConfigurationProviderInterface $configurationProvider,
  ) {
  }

  public function syncLicenseForUser(ID $userId, ?License $license): void {
    if (!$this->configurationProvider->get('image.enableLicensing')) {
      return;
    }

    $images = $this->imageRepository->findByUserId($userId);

    foreach ($images as $imageView) {
      $image = $this->imageStore->get(ID::fromString($imageView->getUuid()));
      $image->updateLicense($license);
      $this->imageStore->store($image);
    }
  }
}
