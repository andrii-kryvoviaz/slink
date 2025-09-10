<?php

declare(strict_types=1);

namespace Slink\User\Application\EventHandler\Demo;

use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\User\Domain\Event\Role\UserGrantedRole;
use Slink\User\Domain\Event\Role\UserRevokedRole;
use Slink\User\Domain\Exception\DemoUserProtectionException;
use Slink\User\Domain\Repository\UserRepositoryInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: UserRevokedRole::class)]
#[AsEventListener(event: UserGrantedRole::class)]
final readonly class DemoUserRoleProtectionListener {

  public function __construct(
    private ConfigurationProviderInterface $configurationProvider,
    private UserRepositoryInterface $userRepository
  ) {}

  public function __invoke(UserRevokedRole|UserGrantedRole $event): void {
    if (!$this->configurationProvider->get('demo.enabled')) {
      return;
    }

    if (!$this->configurationProvider->get('demo.protectDemoUser')) {
      return;
    }

    $user = $this->userRepository->get($event->getId());
    $demoUsername = $this->configurationProvider->get('demo.demoUsername');

    if ($user->getUsername()->toString() === $demoUsername) {
      $action = $event instanceof UserRevokedRole ? 'revoked from' : 'granted to';
      throw new DemoUserProtectionException("Roles cannot be {$action} demo user in demo mode");
    }
  }
}
