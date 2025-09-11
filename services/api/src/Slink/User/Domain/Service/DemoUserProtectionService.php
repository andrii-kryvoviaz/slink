<?php

declare(strict_types=1);

namespace Slink\User\Domain\Service;

use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\User\Domain\Exception\DemoUserProtectionException;
use Slink\User\Domain\User;

final readonly class DemoUserProtectionService {

  public function __construct(
    private ConfigurationProviderInterface $configurationProvider
  ) {
  }

  public function guardAgainstDemoUserModification(User $user, string $operation): void {
    if (!$this->configurationProvider->get('demo.enabled')) {
      return;
    }

    if (!$this->configurationProvider->get('demo.protectDemoUser')) {
      return;
    }

    $isDemoUser = $user->getUsername()->toString() === $this->configurationProvider->get('demo.demoUsername');

    if ($isDemoUser) {
      throw new DemoUserProtectionException("Demo user cannot be {$operation}");
    }
  }
}
