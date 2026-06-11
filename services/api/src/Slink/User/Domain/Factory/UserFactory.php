<?php

declare(strict_types=1);

namespace Slink\User\Domain\Factory;

use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\Context\UserCreationContext;
use Slink\User\Domain\Enum\ApprovalPolicy;
use Slink\User\Domain\Enum\RegistrationPolicy;
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
    RegistrationPolicy $registrationPolicy = RegistrationPolicy::Inherit,
    ApprovalPolicy $approvalPolicy = ApprovalPolicy::Inherit,
  ): User {
    if ($status !== null) {
      return User::create($id, $credentials, $displayName, $status, $this->userCreationContext);
    }

    if (!$registrationPolicy->resolves($this->isRegistrationAllowed())) {
      throw new RegistrationIsNotAllowed();
    }

    $status = $this->resolveStatus($approvalPolicy);

    return User::create($id, $credentials, $displayName, $status, $this->userCreationContext);
  }

  private function resolveStatus(ApprovalPolicy $approvalPolicy): UserStatus {
    if ($approvalPolicy->resolves($this->isApprovalRequired())) {
      return UserStatus::Inactive;
    }

    return UserStatus::Active;
  }

  private function isRegistrationAllowed(): bool {
    return (bool) $this->configurationProvider->get('user.allowRegistration');
  }

  private function isApprovalRequired(): bool {
    return (bool) $this->configurationProvider->get('user.approvalRequired');
  }
}
