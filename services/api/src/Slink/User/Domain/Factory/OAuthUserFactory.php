<?php

declare(strict_types=1);

namespace Slink\User\Domain\Factory;

use SensitiveParameter;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\Contracts\OAuthProviderProfile;
use Slink\User\Domain\Contracts\SuffixableName;
use Slink\User\Domain\Enum\ApprovalPolicy;
use Slink\User\Domain\Enum\RegistrationPolicy;
use Slink\User\Domain\Exception\OAuthEmailRequiredException;
use Slink\User\Domain\Service\UsernameGeneratorInterface;
use Slink\User\Domain\Specification\SignUpUsernameSpecificationInterface;
use Slink\User\Domain\Specification\UniqueDisplayNameSpecificationInterface;
use Slink\User\Domain\Specification\UniqueUsernameSpecificationInterface;
use Slink\User\Domain\User;
use Slink\User\Domain\ValueObject\Auth\Credentials;
use Slink\User\Domain\ValueObject\Auth\HashedPassword;
use Slink\User\Domain\ValueObject\DisplayName;
use Slink\User\Domain\ValueObject\OAuth\OAuthIdentity;
use Slink\User\Domain\ValueObject\Username;

final readonly class OAuthUserFactory {
  private const string DEFAULT_USERNAME = 'user';
  private const int FIRST_USERNAME_SUFFIX = 1;
  private const int FIRST_DISPLAY_NAME_SUFFIX = 2;

  public function __construct(
    private UserFactory $userFactory,
    private UniqueUsernameSpecificationInterface $uniqueUsernameSpecification,
    private SignUpUsernameSpecificationInterface $signUpUsernameSpecification,
    private UsernameGeneratorInterface $usernameGenerator,
    private UniqueDisplayNameSpecificationInterface $uniqueDisplayNameSpecification,
  ) {}

  public function create(#[SensitiveParameter] OAuthIdentity $identity, ?OAuthProviderProfile $provider = null): User {
    $username = $this->resolveUnique($this->baseUsername($identity), $this->uniqueUsernameSpecification->isUnique(...), self::FIRST_USERNAME_SUFFIX);
    $credentials = Credentials::create(
      $identity->getEmail() ?? throw new OAuthEmailRequiredException(),
      $username,
      HashedPassword::encode(bin2hex(random_bytes(32))),
    );
    $displayName = $this->resolveUnique($identity->getDisplayName(), $this->uniqueDisplayNameSpecification->isUnique(...), self::FIRST_DISPLAY_NAME_SUFFIX);

    return $this->userFactory->create(
      ID::generate(),
      $credentials,
      $displayName,
      registrationPolicy: $provider?->getRegistrationPolicy() ?? RegistrationPolicy::Inherit,
      approvalPolicy: $provider?->getApprovalPolicy() ?? ApprovalPolicy::Inherit,
    );
  }

  /**
   * @template T of SuffixableName
   * @param T $base
   * @param callable(T): bool $isUnique
   * @return T
   */
  private function resolveUnique(SuffixableName $base, callable $isUnique, int $firstSuffix): SuffixableName {
    $candidate = $base;

    for ($suffix = $firstSuffix; !$isUnique($candidate); $suffix++) {
      $candidate = $base->withSuffix($suffix);
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
