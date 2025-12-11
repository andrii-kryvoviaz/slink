<?php

declare(strict_types=1);

namespace Slink\User\Domain\Factory;

use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\Context\UserCreationContext;
use Slink\User\Domain\Enum\UserStatus;
use Slink\User\Domain\Repository\UserStoreRepositoryInterface;
use Slink\User\Domain\User;
use Slink\User\Domain\ValueObject\Auth\Credentials;
use Slink\User\Domain\ValueObject\Auth\HashedPassword;
use Slink\User\Domain\ValueObject\DisplayName;
use Slink\User\Domain\ValueObject\Email;
use Slink\User\Domain\ValueObject\Username;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class AdminUserFactory {
  public function __construct(
    private UserCreationContext $userCreationContext,
    private UserStoreRepositoryInterface $userRepository,

    #[\SensitiveParameter]
    #[Autowire(env: 'ADMIN_USERNAME')]
    private string $adminUsername = 'admin',

    #[\SensitiveParameter]
    #[Autowire(env: 'ADMIN_EMAIL')]
    private string $adminEmail = '',

    #[\SensitiveParameter]
    #[Autowire(env: 'ADMIN_PASSWORD')]
    private string $adminPassword = '',
  ) {
  }

  public function createAdminUser(): User {
    $displayName = ucfirst($this->adminUsername);

    $credentials = Credentials::fromCredentials(
      Email::fromString($this->adminEmail),
      Username::fromString($this->adminUsername),
      HashedPassword::encode($this->adminPassword)
    );

    return User::create(
      ID::generate(),
      $credentials,
      DisplayName::fromString($displayName),
      UserStatus::Active,
      $this->userCreationContext
    );
  }

  public function getAdminUsername(): string {
    return $this->adminUsername;
  }

  public function getAdminEmail(): string {
    return $this->adminEmail;
  }

  public function hasValidConfiguration(): bool {
    return !empty($this->adminEmail) && !empty($this->adminPassword);
  }

  public function isMissingPassword(): bool {
    return !empty($this->adminEmail) && empty($this->adminPassword);
  }

  public function adminAlreadyExists(): bool {
    $byUsername = $this->userRepository->getByUsername(Username::fromString($this->adminUsername));
    if ($byUsername !== null) {
      return true;
    }

    $byEmail = $this->userRepository->getByUsername(Email::fromString($this->adminEmail));
    return $byEmail !== null;
  }
}
