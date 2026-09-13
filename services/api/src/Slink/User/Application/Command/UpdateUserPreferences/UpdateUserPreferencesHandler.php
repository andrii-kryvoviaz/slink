<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\UpdateUserPreferences;

use Slink\Image\Application\Service\LicenseSyncServiceInterface;
use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\Repository\UserStoreRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final readonly class UpdateUserPreferencesHandler implements CommandHandlerInterface {
  public function __construct(
    private UserStoreRepositoryInterface $userStore,
    private LicenseSyncServiceInterface $licenseSyncService,
    private NormalizerInterface $normalizer,
  ) {
  }

  public function __invoke(UpdateUserPreferencesCommand $command, string $userId): void {
    $user = $this->userStore->get(ID::fromString($userId));

    /** @var array<string, string|bool|null> $changes */
    $changes = $this->normalizer->normalize($command, null, [
      AbstractNormalizer::IGNORED_ATTRIBUTES => ['syncLicenseToImages'],
    ]);

    $preferences = $user->getPreferences()->applyChanges($changes);
    $user->updatePreferences($preferences);
    $this->userStore->store($user);

    if (!($command->syncLicenseToImages ?? false)) {
      return;
    }

    $this->licenseSyncService->syncLicenseForUser(ID::fromString($userId), $preferences->getDefaultLicense());
  }
}
