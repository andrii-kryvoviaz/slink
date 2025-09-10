<?php

declare(strict_types=1);

namespace Slink\User\Application\EventHandler\Demo;

use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\User\Domain\Event\UserStatusWasChanged;
use Slink\User\Domain\Exception\DemoUserProtectionException;
use Slink\User\Domain\Repository\UserRepositoryInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: UserStatusWasChanged::class)]
final readonly class DemoUserStatusProtectionListener {

  public function __construct(
    private ConfigurationProviderInterface $configurationProvider,
    private UserRepositoryInterface $userRepository
  ) {}

  public function __invoke(UserStatusWasChanged $event): void {
    if (!$this->configurationProvider->get('demo.enabled')) {
      return;
    }

    if (!$this->configurationProvider->get('demo.protectDemoUser')) {
      return;
    }

    $user = $this->userRepository->get($event->getId());
    $demoUsername = $this->configurationProvider->get('demo.demoUsername');

    if ($user->getUsername()->toString() === $demoUsername) {
      throw new DemoUserProtectionException('Demo user status cannot be changed in demo mode');
    }
  }
}
