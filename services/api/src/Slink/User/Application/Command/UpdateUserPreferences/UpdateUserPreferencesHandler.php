<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\UpdateUserPreferences;

use Slink\Image\Domain\Enum\License;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Image\Domain\Repository\ImageStoreRepositoryInterface;
use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\Repository\UserStoreRepositoryInterface;
use Slink\User\Infrastructure\ReadModel\Repository\UserPreferencesRepository;

final readonly class UpdateUserPreferencesHandler implements CommandHandlerInterface {
  public function __construct(
    private UserStoreRepositoryInterface $userStore,
    private UserPreferencesRepository $preferencesRepository,
    private ImageRepositoryInterface $imageRepository,
    private ImageStoreRepositoryInterface $imageStore,
  ) {
  }

  public function __invoke(UpdateUserPreferencesCommand $command, string $userId): void {
    $user = $this->userStore->get(ID::fromString($userId));
    
    $existingPrefs = $this->preferencesRepository->findByUserId($userId);
    $preferences = $existingPrefs 
      ? $existingPrefs->getPreferences()->with('defaultLicense', $command->getDefaultLicense())
      : $command->getPreferences();
    
    $user->updatePreferences($preferences);
    $this->userStore->store($user);
    
    if ($command->shouldSyncLicenseToImages()) {
      $this->syncLicenseToImages(ID::fromString($userId), $command->getDefaultLicense());
    }
  }

  private function syncLicenseToImages(ID $userId, ?License $license): void {
    $images = $this->imageRepository->findByUserId($userId);
    
    foreach ($images as $imageView) {
      $image = $this->imageStore->get(ID::fromString($imageView->getUuid()));
      $image->updateLicense($license);
      $this->imageStore->store($image);
    }
  }
}
