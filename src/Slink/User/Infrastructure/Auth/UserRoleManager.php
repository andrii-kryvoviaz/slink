<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\Auth;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use Slink\User\Application\Service\UserRoleManagerInterface;

final readonly class UserRoleManager implements UserRoleManagerInterface {
  
  public function __construct(private CacheItemPoolInterface $userPermissionsVersion) {
  }
  
  /**
   * @throws InvalidArgumentException
   */
  public function storePermissionsVersion(string $uuid, int $version): void {
    $cacheItem = $this->userPermissionsVersion->getItem($uuid);
    $cacheItem->set($version);
    
    $this->userPermissionsVersion->save($cacheItem);
  }
  
  /**
   * @throws InvalidArgumentException
   */
  public function getPermissionsVersion(string $uuid): ?int {
    $cacheItem = $this->userPermissionsVersion->getItem($uuid);
    
    return $cacheItem->isHit()
      ? $cacheItem->get()
      : null;
  }
}