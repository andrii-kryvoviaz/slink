<?php

declare(strict_types=1);

namespace Unit\Slink\User\Application\Service;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\Context\UserCreationContext;
use Slink\User\Domain\Contracts\OAuthProviderProfile;
use Slink\User\Domain\Enum\ApprovalPolicy;
use Slink\User\Domain\Enum\RegistrationPolicy;
use Slink\User\Domain\Factory\OAuthUserFactory;
use Slink\User\Domain\Factory\UserFactory;
use Slink\User\Domain\Service\UsernameGeneratorInterface;
use Slink\User\Domain\Specification\SignUpUsernameSpecification;
use Slink\User\Domain\Specification\UniqueDisplayNameSpecificationInterface;
use Slink\User\Domain\Specification\UniqueEmailSpecificationInterface;
use Slink\User\Domain\Specification\UniqueUsernameSpecificationInterface;
use Slink\User\Domain\User;
use Slink\User\Domain\ValueObject\Auth\Credentials;
use Slink\User\Domain\ValueObject\DisplayName;
use Slink\User\Domain\ValueObject\Email;
use Slink\User\Domain\ValueObject\OAuth\OAuthIdentity;
use Slink\User\Domain\ValueObject\OAuth\OAuthSubject;
use Slink\User\Domain\ValueObject\OAuth\ProviderSlug;
use Slink\User\Domain\ValueObject\OAuth\TokenClaims;
use Slink\User\Domain\ValueObject\Username;

final class OAuthUserFactoryTest extends TestCase {
  private const string GENERATED_USERNAME = 'andrii.kryvoviaz';

  #[Test]
  public function itCreatesUserWithClaimsData(): void {
    $identity = $this->createIdentity();

    $uniqueUsernameSpec = $this->createStub(UniqueUsernameSpecificationInterface::class);
    $uniqueUsernameSpec->method('isUnique')->willReturn(true);

    $factory = $this->createFactory($this->createRealUserFactory(), $uniqueUsernameSpec);

    $result = $factory->create($identity);

    $this->assertInstanceOf(User::class, $result);
  }

  #[Test]
  public function itHandlesUsernameCollision(): void {
    $identity = $this->createIdentity();

    $callCount = 0;
    $uniqueUsernameSpec = $this->createStub(UniqueUsernameSpecificationInterface::class);
    $uniqueUsernameSpec->method('isUnique')
      ->willReturnCallback(function (Username $username) use (&$callCount) {
        $callCount++;
        if ($callCount === 1) {
          return false;
        }
        return true;
      });

    $factory = $this->createFactory($this->createRealUserFactory(), $uniqueUsernameSpec);

    $result = $factory->create($identity);

    $this->assertInstanceOf(User::class, $result);
    $this->assertGreaterThan(1, $callCount);
  }

  #[Test]
  public function itTruncatesMaxLengthUsernameOnCollision(): void {
    $identity = $this->createIdentity(str_repeat('a', 30));

    $callCount = 0;
    $uniqueUsernameSpec = $this->createStub(UniqueUsernameSpecificationInterface::class);
    $uniqueUsernameSpec->method('isUnique')
      ->willReturnCallback(function (Username $username) use (&$callCount) {
        $callCount++;
        return $callCount > 1;
      });

    $userFactory = $this->createMock(UserFactory::class);
    $userFactory->expects($this->once())
      ->method('create')
      ->with(
        $this->isInstanceOf(ID::class),
        $this->callback(
          fn(Credentials $credentials): bool => $credentials->username->toString() === str_repeat('a', 29) . '1',
        ),
      );

    $factory = $this->createFactory($userFactory, $uniqueUsernameSpec, $this->generatedUsername(str_repeat('a', 30)));

    $factory->create($identity);
  }

  #[Test]
  public function itForwardsProviderPoliciesToUserFactory(): void {
    $identity = $this->createIdentity();

    $provider = $this->createStub(OAuthProviderProfile::class);
    $provider->method('getRegistrationPolicy')->willReturn(RegistrationPolicy::Allowed);
    $provider->method('getApprovalPolicy')->willReturn(ApprovalPolicy::Required);

    $userFactory = $this->createMock(UserFactory::class);
    $userFactory->expects($this->once())
      ->method('create')
      ->with(
        $this->isInstanceOf(ID::class),
        $this->isInstanceOf(Credentials::class),
        $identity->getDisplayName(),
        null,
        RegistrationPolicy::Allowed,
        ApprovalPolicy::Required,
      );

    $uniqueUsernameSpec = $this->createStub(UniqueUsernameSpecificationInterface::class);
    $uniqueUsernameSpec->method('isUnique')->willReturn(true);

    $factory = $this->createFactory($userFactory, $uniqueUsernameSpec);

    $factory->create($identity, $provider);
  }

  #[Test]
  public function itDefaultsPoliciesToInheritWhenProviderIsMissing(): void {
    $identity = $this->createIdentity();

    $userFactory = $this->createMock(UserFactory::class);
    $userFactory->expects($this->once())
      ->method('create')
      ->with(
        $this->isInstanceOf(ID::class),
        $this->isInstanceOf(Credentials::class),
        $identity->getDisplayName(),
        null,
        RegistrationPolicy::Inherit,
        ApprovalPolicy::Inherit,
      );

    $uniqueUsernameSpec = $this->createStub(UniqueUsernameSpecificationInterface::class);
    $uniqueUsernameSpec->method('isUnique')->willReturn(true);

    $factory = $this->createFactory($userFactory, $uniqueUsernameSpec);

    $factory->create($identity);
  }

  /**
   * @return array<string, array{array<string, mixed>}>
   */
  public static function provideUnusablePreferredUsernameClaims(): array {
    return [
      'claim missing' => [['name' => 'Andrii Kryvoviaz']],
      'empty' => [['name' => 'Andrii Kryvoviaz', 'preferred_username' => '']],
      'too short' => [['name' => 'Andrii Kryvoviaz', 'preferred_username' => 'ab']],
      'too long' => [['name' => 'Andrii Kryvoviaz', 'preferred_username' => str_repeat('a', 31)]],
      'not a string' => [['name' => 'Andrii Kryvoviaz', 'preferred_username' => 12345]],
    ];
  }

  #[Test]
  #[Group('sc-1129')]
  public function itTakesUsernameFromPreferredUsernameWhenNameIsAlsoPresent(): void {
    $identity = $this->createIdentityFromClaims([
      'name' => 'Andrii Kryvoviaz',
      'preferred_username' => 'andy',
    ]);

    $factory = $this->createFactory($this->createRealUserFactory(), $this->usernamesTaken());

    $user = $factory->create($identity);

    $this->assertSame('andy', $user->getUsername()->toString());
  }

  #[Test]
  #[Group('sc-1129')]
  public function itKeepsDisplayNameFromNameWhenPreferredUsernameIsUsed(): void {
    $identity = $this->createIdentityFromClaims([
      'name' => 'Andrii Kryvoviaz',
      'preferred_username' => 'andy',
    ]);

    $userFactory = $this->createMock(UserFactory::class);
    $userFactory->expects($this->once())
      ->method('create')
      ->with(
        $this->isInstanceOf(ID::class),
        $this->callback(fn(Credentials $credentials): bool => $credentials->username->toString() === 'andy'),
        $this->callback(fn(DisplayName $displayName): bool => $displayName->toString() === 'Andrii Kryvoviaz'),
      );

    $factory = $this->createFactory($userFactory, $this->usernamesTaken());

    $factory->create($identity);
  }

  #[Test]
  #[Group('sc-1129')]
  #[Group('sc-1144')]
  public function itLowercasesPreferredUsername(): void {
    $identity = $this->createIdentityFromClaims([
      'name' => 'Andrii Kryvoviaz',
      'preferred_username' => 'Andy',
    ]);

    $factory = $this->createFactory($this->createRealUserFactory(), $this->usernamesTaken());

    $user = $factory->create($identity);

    $this->assertSame('andy', $user->getUsername()->toString());
    $this->assertSame('Andrii Kryvoviaz', $user->getDisplayName()->toString());
  }

  #[Test]
  #[Group('sc-1144')]
  public function itTakesPreferredUsernameWithSingleSpecialCharacter(): void {
    $identity = $this->createIdentityFromClaims([
      'name' => 'Andrii Kryvoviaz',
      'preferred_username' => 'john.doe',
    ]);

    $factory = $this->createFactory($this->createRealUserFactory(), $this->usernamesTaken());

    $user = $factory->create($identity);

    $this->assertSame('john.doe', $user->getUsername()->toString());
  }

  /**
   * @return array<string, array{string}>
   */
  public static function provideSignUpRejectedPreferredUsernames(): array {
    return [
      'email-shaped' => ['user@corp.com'],
      'contains a space' => ['John Doe'],
      'adjacent special characters' => ['john__doe'],
      'reserved name in any casing' => ['Anonymous'],
      'too short' => ['jo'],
      'too long' => [str_repeat('a', 31)],
    ];
  }

  #[Test]
  #[Group('sc-1144')]
  #[DataProvider('provideSignUpRejectedPreferredUsernames')]
  public function itFallsBackToDisplayNameUsernameWhenSignUpRulesRejectPreferredUsername(string $preferredUsername): void {
    $identity = $this->createIdentityFromClaims([
      'name' => 'Andrii Kryvoviaz',
      'preferred_username' => $preferredUsername,
    ]);

    $factory = $this->createFactory($this->createRealUserFactory(), $this->usernamesTaken());

    $user = $factory->create($identity);

    $this->assertSame('andrii.kryvoviaz', $user->getUsername()->toString());
  }

  #[Test]
  #[Group('sc-1144')]
  public function itSuffixesTakenLowercasedPreferredUsername(): void {
    $identity = $this->createIdentityFromClaims([
      'name' => 'Andrii Kryvoviaz',
      'preferred_username' => 'Andy',
    ]);

    $factory = $this->createFactory($this->createRealUserFactory(), $this->usernamesTaken('andy'));

    $user = $factory->create($identity);

    $this->assertSame('andy1', $user->getUsername()->toString());
  }

  #[Test]
  public function itSuffixesTakenGeneratedUsername(): void {
    $identity = $this->createIdentityFromClaims(['name' => "O'Neil Smith"]);

    $factory = $this->createFactory(
      $this->createRealUserFactory(),
      $this->usernamesTaken('o.neil.smith'),
      $this->generatedUsername('o.neil.smith'),
    );

    $user = $factory->create($identity);

    $this->assertSame('o.neil.smith1', $user->getUsername()->toString());
  }

  #[Test]
  public function itFallsBackToDefaultUsernameWhenSignUpRulesRejectGeneratedUsername(): void {
    $identity = $this->createIdentityFromClaims(['name' => 'Anonymous!']);

    $factory = $this->createFactory(
      $this->createRealUserFactory(),
      $this->usernamesTaken(),
      $this->generatedUsername('anonymous'),
    );

    $user = $factory->create($identity);

    $this->assertSame('user', $user->getUsername()->toString());
  }

  #[Test]
  public function itFallsBackToDefaultUsernameWhenDisplayNameLeavesNothingUsable(): void {
    $identity = $this->createIdentityFromClaims(['name' => '!?!', 'preferred_username' => 'a__b']);

    $factory = $this->createFactory($this->createRealUserFactory(), $this->usernamesTaken(), $this->generatedUsername(null));

    $user = $factory->create($identity);

    $this->assertSame('user', $user->getUsername()->toString());
  }

  #[Test]
  public function itSuffixesTakenDefaultUsername(): void {
    $identity = $this->createIdentityFromClaims(['name' => '!?!']);

    $factory = $this->createFactory(
      $this->createRealUserFactory(),
      $this->usernamesTaken('user', 'user1'),
      $this->generatedUsername(null),
    );

    $user = $factory->create($identity);

    $this->assertSame('user2', $user->getUsername()->toString());
  }

  /**
   * @param array<string, mixed> $claims
   */
  #[Test]
  #[Group('sc-1129')]
  #[DataProvider('provideUnusablePreferredUsernameClaims')]
  public function itFallsBackToDisplayNameUsernameWhenPreferredUsernameIsUnusable(array $claims): void {
    $identity = $this->createIdentityFromClaims($claims);

    $factory = $this->createFactory($this->createRealUserFactory(), $this->usernamesTaken());

    $user = $factory->create($identity);

    $this->assertSame('andrii.kryvoviaz', $user->getUsername()->toString());
  }

  #[Test]
  #[Group('sc-1129')]
  public function itSuffixesTakenPreferredUsername(): void {
    $identity = $this->createIdentityFromClaims([
      'name' => 'Andrii Kryvoviaz',
      'preferred_username' => 'andy',
    ]);

    $factory = $this->createFactory($this->createRealUserFactory(), $this->usernamesTaken('andy'));

    $user = $factory->create($identity);

    $this->assertSame('andy1', $user->getUsername()->toString());
  }

  #[Test]
  #[Group('sc-1129')]
  public function itTruncatesTakenMaxLengthPreferredUsernameWhenSuffixing(): void {
    $preferredUsername = str_repeat('b', 30);
    $identity = $this->createIdentityFromClaims([
      'name' => 'Andrii Kryvoviaz',
      'preferred_username' => $preferredUsername,
    ]);

    $factory = $this->createFactory($this->createRealUserFactory(), $this->usernamesTaken($preferredUsername));

    $user = $factory->create($identity);

    $this->assertSame(str_repeat('b', 29) . '1', $user->getUsername()->toString());
  }

  private function createFactory(
    UserFactory $userFactory,
    UniqueUsernameSpecificationInterface $uniqueUsernameSpec,
    ?UsernameGeneratorInterface $usernameGenerator = null,
  ): OAuthUserFactory {
    return new OAuthUserFactory(
      $userFactory,
      $uniqueUsernameSpec,
      new SignUpUsernameSpecification(),
      $usernameGenerator ?? $this->generatedUsername(self::GENERATED_USERNAME),
    );
  }

  private function generatedUsername(?string $username): UsernameGeneratorInterface {
    $usernameGenerator = $this->createStub(UsernameGeneratorInterface::class);
    $usernameGenerator->method('generate')->willReturn($username === null ? null : Username::fromString($username));

    return $usernameGenerator;
  }

  /**
   * @param array<string, mixed> $claims
   */
  private function createIdentityFromClaims(array $claims): OAuthIdentity {
    return OAuthIdentity::fromTokenClaims(
      TokenClaims::fromPayload([
        'iss' => 'https://id.example.com',
        'sub' => 'subject-1',
        'email' => 'andy@example.com',
        'email_verified' => true,
        ...$claims,
      ]),
      ProviderSlug::fromString('pocket-id'),
    );
  }

  private function usernamesTaken(string ...$taken): UniqueUsernameSpecificationInterface {
    $uniqueUsernameSpec = $this->createStub(UniqueUsernameSpecificationInterface::class);
    $uniqueUsernameSpec->method('isUnique')
      ->willReturnCallback(fn(Username $username): bool => !in_array($username->toString(), $taken, true));

    return $uniqueUsernameSpec;
  }

  private function createIdentity(string $displayName = 'Test User'): OAuthIdentity {
    $identity = $this->createStub(OAuthIdentity::class);
    $identity->method('getEmail')->willReturn(Email::fromString('user@example.com'));
    $identity->method('getDisplayName')->willReturn(DisplayName::fromString($displayName));
    $identity->method('getSubject')->willReturn($this->createStub(OAuthSubject::class));

    return $identity;
  }

  private function createRealUserFactory(): UserFactory {
    $configProvider = $this->createStub(ConfigurationProviderInterface::class);
    $configProvider->method('get')->willReturnCallback(fn(string $key) => match ($key) {
      'user.allowRegistration' => true,
      'user.approvalRequired' => false,
      default => null,
    });

    $uniqueEmailSpec = $this->createStub(UniqueEmailSpecificationInterface::class);
    $uniqueEmailSpec->method('isUnique')->willReturn(true);

    $uniqueUsernameSpec = $this->createStub(UniqueUsernameSpecificationInterface::class);
    $uniqueUsernameSpec->method('isUnique')->willReturn(true);

    $uniqueDisplayNameSpec = $this->createStub(UniqueDisplayNameSpecificationInterface::class);
    $uniqueDisplayNameSpec->method('isUnique')->willReturn(true);

    $context = new UserCreationContext($uniqueEmailSpec, $uniqueUsernameSpec, $uniqueDisplayNameSpec);

    return new UserFactory($configProvider, $context);
  }
}
