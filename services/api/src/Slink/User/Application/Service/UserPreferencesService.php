<?php

declare(strict_types=1);

namespace Slink\User\Application\Service;

use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\Repository\UserPreferencesRepositoryInterface;
use Slink\User\Domain\ValueObject\UserPreferences;

final readonly class UserPreferencesService {
  public function __construct(
    private UserPreferencesRepositoryInterface $repository,
  ) {}

  public function getForUser(?ID $userId): UserPreferences {
    if ($userId === null) {
      return UserPreferences::empty();
    }

    return $this->repository
      ->findByUserId($userId->toString())
      ?->getPreferences()
      ?? UserPreferences::empty();
  }
}
