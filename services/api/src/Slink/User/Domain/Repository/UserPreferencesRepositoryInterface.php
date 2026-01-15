<?php

declare(strict_types=1);

namespace Slink\User\Domain\Repository;

use Slink\User\Infrastructure\ReadModel\View\UserPreferencesView;

interface UserPreferencesRepositoryInterface {
  public function findByUserId(string $userId): ?UserPreferencesView;
  public function save(UserPreferencesView $preferences): void;
}
