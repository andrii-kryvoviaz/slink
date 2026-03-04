<?php

declare(strict_types=1);

namespace Slink\User\Domain\Factory;

use SensitiveParameter;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\User;
use Slink\User\Domain\ValueObject\Auth\Credentials;
use Slink\User\Domain\ValueObject\Auth\HashedPassword;
use Slink\User\Domain\ValueObject\OAuth\OAuthIdentity;
use Slink\User\Domain\Exception\OAuthEmailRequiredException;
use Slink\User\Domain\ValueObject\Username;
use Slink\User\Domain\Specification\UniqueUsernameSpecificationInterface;
use Slink\User\Domain\ValueObject\DisplayName;

final readonly class OAuthUserFactory {
  public function __construct(
    private UserFactory $userFactory,
    private UniqueUsernameSpecificationInterface $uniqueUsernameSpecification,
  ) {}

  public function create(#[SensitiveParameter] OAuthIdentity $identity): User {
    $username = $this->resolveUniqueUsername($identity->getDisplayName());
    $credentials = Credentials::create(
      $identity->getEmail() ?? throw new OAuthEmailRequiredException(),
      $username,
      HashedPassword::encode(bin2hex(random_bytes(32))),
    );

    return $this->userFactory->create(ID::generate(), $credentials, $identity->getDisplayName());
  }

  private function resolveUniqueUsername(DisplayName $displayName): Username {
    $base = Username::fromDisplayName($displayName);

    if ($this->uniqueUsernameSpecification->isUnique($base)) {
      return $base;
    }

    $i = 1;
    do {
      $candidate = Username::fromString($base->toString() . $i);
      $i++;
    } while (!$this->uniqueUsernameSpecification->isUnique($candidate));

    return $candidate;
  }
}
