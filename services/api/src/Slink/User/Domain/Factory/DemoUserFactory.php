<?php

declare(strict_types=1);

namespace Slink\User\Domain\Factory;

use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\Context\ChangeUserRoleContext;
use Slink\User\Domain\Context\UserCreationContext;
use Slink\User\Domain\User;
use Slink\User\Domain\Enum\UserRole;
use Slink\User\Domain\Enum\UserStatus;
use Slink\User\Domain\ValueObject\Auth\Credentials;
use Slink\User\Domain\ValueObject\DisplayName;
use Slink\User\Domain\ValueObject\Role;
use Slink\User\Domain\ValueObject\Username;

final readonly class DemoUserFactory {

  public function __construct(
    private ConfigurationProviderInterface $configurationProvider,
    private UserCreationContext $userCreationContext,
    private ChangeUserRoleContext $changeUserRoleContext
  ) {}

  public function createDemoUser(?string $username = null, ?string $password = null, ?string $displayName = null, ?string $email = null): User {
    $finalUsername = $username ?? $this->configurationProvider->get('demo.demoUsername');
    $finalPassword = $password ?? $this->configurationProvider->get('demo.demoPassword'); 
    $finalDisplayName = $displayName ?? $this->configurationProvider->get('demo.demoDisplayName');
    $finalEmail = $email ?? 'demo@demo.local';

    $credentials = Credentials::fromPlainCredentials(
      $finalEmail,
      $finalUsername,
      $finalPassword
    );

    $user = User::create(
      ID::generate(),
      $credentials,
      DisplayName::fromString($finalDisplayName),
      UserStatus::Active,
      $this->userCreationContext
    );

    $user->grantRole(
      Role::fromString(UserRole::Admin->value),
      $this->changeUserRoleContext
    );

    return $user;
  }

  public function getDemoUserCredentials(): array {
    return [
      'username' => $this->configurationProvider->get('demo.demoUsername'),
      'password' => $this->configurationProvider->get('demo.demoPassword'),
      'displayName' => $this->configurationProvider->get('demo.demoDisplayName'),
      'email' => 'demo@demo.local'
    ];
  }

  public function isDemoUser(string $username): bool {
    return $username === $this->configurationProvider->get('demo.demoUsername');
  }
}
