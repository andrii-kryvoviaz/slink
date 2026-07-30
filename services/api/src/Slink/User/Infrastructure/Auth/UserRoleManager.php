<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\Auth;

use Doctrine\ORM\NonUniqueResultException;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\User\Application\Service\UserRoleManagerInterface;
use Slink\User\Domain\Repository\UserRepositoryInterface;
use Slink\User\Domain\ValueObject\Auth\PermissionsVersion;

final readonly class UserRoleManager implements UserRoleManagerInterface {

  public function __construct(
    private CacheItemPoolInterface $userPermissionsVersion,
    private UserRepositoryInterface $userRepository,
  ) {
  }
  
  /**
   * @throws InvalidArgumentException
   */
  public function storePermissionsVersion(string $uuid, PermissionsVersion $version): void {
    $cacheItem = $this->userPermissionsVersion->getItem($uuid);
    $cacheItem->set($version);

    $this->userPermissionsVersion->save($cacheItem);
  }

  /**
   * @throws InvalidArgumentException
   */
  public function getPermissionsVersion(string $uuid): PermissionsVersion {
    $cachedVersion = $this->userPermissionsVersion->getItem($uuid)->get();

    if ($cachedVersion instanceof PermissionsVersion) {
      return $cachedVersion;
    }

    if ($this->isRestricted($uuid)) {
      return PermissionsVersion::terminal();
    }

    $version = PermissionsVersion::never();
    $this->storePermissionsVersion($uuid, $version);

    return $version;
  }

  private function isRestricted(string $uuid): bool {
    try {
      return $this->userRepository->one(ID::fromString($uuid))->isRestricted();
    } catch (NonUniqueResultException|NotFoundException) {
      return true;
    }
  }
}