<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\UpdateUserPreferences;

use Slink\Image\Application\Service\LicenseSyncServiceInterface;
use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Application\Lock\ResourceLock;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\Repository\UserStoreRepositoryInterface;
use Slink\User\Domain\ValueObject\UserPreferences;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final readonly class UpdateUserPreferencesHandler implements CommandHandlerInterface {
  public function __construct(
    private UserStoreRepositoryInterface $userStore,
    private LicenseSyncServiceInterface $licenseSyncService,
    private NormalizerInterface $normalizer,
    private ResourceLock $lock,
  ) {
  }

  public function __invoke(UpdateUserPreferencesCommand $command, string $userId): void {
    $preferences = $this->storeChanges($command, $userId);

    if (!$command->shouldSyncLicenseToImages()) {
      return;
    }

    $this->licenseSyncService->syncLicenseForUser(ID::fromString($userId), $preferences->getDefaultLicense());
  }

  private function storeChanges(UpdateUserPreferencesCommand $command, string $userId): UserPreferences {
    return $this->lock->run('user-preferences-' . $userId, function () use ($command, $userId): UserPreferences {
      $user = $this->userStore->get(ID::fromString($userId));

      /** @var array<string, string|bool|null> $changes */
      $changes = $this->normalizer->normalize($command);

      $preferences = $user->getPreferences()->applyChanges($changes);
      $user->updatePreferences($preferences);
      $this->userStore->store($user);

      return $preferences;
    });
  }
}
