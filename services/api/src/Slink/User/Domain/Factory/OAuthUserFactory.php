<?php

declare(strict_types=1);

namespace Slink\User\Domain\Factory;

use SensitiveParameter;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\Contracts\OAuthProviderProfile;
use Slink\User\Domain\Enum\ApprovalPolicy;
use Slink\User\Domain\Enum\RegistrationPolicy;
use Slink\User\Domain\Exception\OAuthEmailRequiredException;
use Slink\User\Domain\Service\UsernameGeneratorInterface;
use Slink\User\Domain\Specification\SignUpUsernameSpecificationInterface;
use Slink\User\Domain\Specification\UniqueUsernameSpecificationInterface;
use Slink\User\Domain\User;
use Slink\User\Domain\ValueObject\Auth\Credentials;
use Slink\User\Domain\ValueObject\Auth\HashedPassword;
use Slink\User\Domain\ValueObject\OAuth\OAuthIdentity;
use Slink\User\Domain\ValueObject\Username;

final readonly class OAuthUserFactory {
  private const string DEFAULT_USERNAME = 'user';

  public function __construct(
    private UserFactory $userFactory,
    private UniqueUsernameSpecificationInterface $uniqueUsernameSpecification,
    private SignUpUsernameSpecificationInterface $signUpUsernameSpecification,
    private UsernameGeneratorInterface $usernameGenerator,
  ) {}

  public function create(#[SensitiveParameter] OAuthIdentity $identity, ?OAuthProviderProfile $provider = null): User {
    $username = $this->resolveUniqueUsername($identity);
    $credentials = Credentials::create(
      $identity->getEmail() ?? throw new OAuthEmailRequiredException(),
      $username,
      HashedPassword::encode(bin2hex(random_bytes(32))),
    );

    return $this->userFactory->create(
      ID::generate(),
      $credentials,
      $identity->getDisplayName(),
      registrationPolicy: $provider?->getRegistrationPolicy() ?? RegistrationPolicy::Inherit,
      approvalPolicy: $provider?->getApprovalPolicy() ?? ApprovalPolicy::Inherit,
    );
  }

  private function resolveUniqueUsername(OAuthIdentity $identity): Username {
    $base = $this->baseUsername($identity);
    $candidate = $base;
    $suffix = 1;

    while (!$this->uniqueUsernameSpecification->isUnique($candidate)) {
      $candidate = $base->withSuffix($suffix);
      $suffix++;
    }

    return $candidate;
  }

  private function baseUsername(OAuthIdentity $identity): Username {
    $candidate = array_find(
      [
        mb_strtolower($identity->getPreferredUsername() ?? ''),
        $this->usernameGenerator->generate($identity->getDisplayName())?->toString() ?? '',
      ],
      $this->signUpUsernameSpecification->isSatisfiedBy(...),
    );

    return Username::fromString($candidate ?? self::DEFAULT_USERNAME);
  }
}
