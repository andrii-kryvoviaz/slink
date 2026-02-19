<?php

declare(strict_types=1);

namespace Slink\User\Domain\Factory;

use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\Context\UserCreationContext;
use Slink\User\Domain\Enum\UserStatus;
use Slink\User\Domain\Exception\RegistrationIsNotAllowed;
use Slink\User\Domain\User;
use Slink\User\Domain\ValueObject\Auth\Credentials;
use Slink\User\Domain\ValueObject\DisplayName;

final readonly class UserFactory {
  public function __construct(
    private ConfigurationProviderInterface $configurationProvider,
    private UserCreationContext $userCreationContext,
  ) {}

  public function create(
    ID $id,
    Credentials $credentials,
    DisplayName $displayName,
    ?UserStatus $status = null,
  ): User {
    if (!$this->configurationProvider->get('user.allowRegistration')) {
      throw new RegistrationIsNotAllowed();
    }

    if ($status === null) {
      $status = $this->configurationProvider->get('user.approvalRequired')
        ? UserStatus::Inactive
        : UserStatus::Active;
    }

    return User::create($id, $credentials, $displayName, $status, $this->userCreationContext);
  }
}
