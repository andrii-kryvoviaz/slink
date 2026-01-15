<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\UpdateUserPreferences;

use Slink\Image\Application\Service\LicenseSyncServiceInterface;
use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\Repository\UserPreferencesRepositoryInterface;
use Slink\User\Domain\Repository\UserStoreRepositoryInterface;

final readonly class UpdateUserPreferencesHandler implements CommandHandlerInterface {
  public function __construct(
    private UserStoreRepositoryInterface $userStore,
    private UserPreferencesRepositoryInterface $preferencesRepository,
    private LicenseSyncServiceInterface $licenseSyncService,
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
      $this->licenseSyncService->syncLicenseForUser(ID::fromString($userId), $command->getDefaultLicense());
    }
  }
}
