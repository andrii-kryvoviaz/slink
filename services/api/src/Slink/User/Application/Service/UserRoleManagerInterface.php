<?php

declare(strict_types=1);

namespace Slink\User\Application\Service;

use Slink\User\Domain\ValueObject\Auth\PermissionsVersion;

interface UserRoleManagerInterface {
  public function storePermissionsVersion(string $uuid, PermissionsVersion $version): void;
  public function getPermissionsVersion(string $uuid): PermissionsVersion;
}
