<?php

declare(strict_types=1);

namespace Slink\User\Application\EventHandler\Demo;

use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Application\Command\CommandBusInterface;
use Slink\User\Application\Command\ResetDemoEnvironment\ResetDemoEnvironmentCommand;
use Slink\User\Domain\Event\UserWasDeleted;
use Slink\User\Domain\Exception\DemoUserProtectionException;
use Slink\User\Domain\Repository\UserRepositoryInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: UserWasDeleted::class)]
final readonly class DemoUserProtectionListener {

  public function __construct(
    private ConfigurationProviderInterface $configurationProvider,
    private UserRepositoryInterface $userRepository
  ) {}

  public function __invoke(UserWasDeleted $event): void {
    if (!$this->configurationProvider->get('demo.enabled')) {
      return;
    }

    if (!$this->configurationProvider->get('demo.protectDemoUser')) {
      return;
    }

    $user = $this->userRepository->get($event->getId());
    $demoUsername = $this->configurationProvider->get('demo.demoUsername');

    if ($user->getUsername()->toString() === $demoUsername) {
      throw new DemoUserProtectionException('Demo user cannot be deleted in demo mode');
    }
  }
}
