<?php

declare(strict_types=1);

namespace Slink\User\Application\Service;

interface UserRoleManagerInterface {
  public function storePermissionsVersion(string $uuid, int $version): void;
  public function getPermissionsVersion(string $uuid): ?int;
}