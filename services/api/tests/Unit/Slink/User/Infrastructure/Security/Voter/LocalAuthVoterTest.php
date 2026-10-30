<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\User\Infrastructure\Security\Voter;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\User\Infrastructure\Security\Voter\LocalAuthVoter;
use Symfony\Component\Security\Core\Authentication\Token\NullToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\User\InMemoryUser;

#[Group('sc-1186')]
final class LocalAuthVoterTest extends TestCase {
  /**
   * @return iterable<string, array{TokenInterface}>
   */
  public static function tokenProvider(): iterable {
    yield 'anonymous caller' => [new NullToken()];
    yield 'signed-in user' => [self::tokenWithRoles(['ROLE_USER'])];
    yield 'signed-in admin' => [self::tokenWithRoles(['ROLE_USER', 'ROLE_ADMIN'])];
  }

  #[Test]
  public function itNamesTheAttribute(): void {
    self::assertSame('LOCAL_AUTH_ENABLED', LocalAuthVoter::LOCAL_AUTH_ENABLED);
  }

  #[Test]
  #[DataProvider('tokenProvider')]
  public function itGrantsWhenLocalAuthIsEnabled(TokenInterface $token): void {
    $voter = new LocalAuthVoter($this->configurationWith(true));

    $result = $voter->vote($token, null, [LocalAuthVoter::LOCAL_AUTH_ENABLED]);

    self::assertSame(VoterInterface::ACCESS_GRANTED, $result);
  }

  #[Test]
  #[DataProvider('tokenProvider')]
  public function itDeniesWhenLocalAuthIsDisabled(TokenInterface $token): void {
    $voter = new LocalAuthVoter($this->configurationWith(false));

    $result = $voter->vote($token, null, [LocalAuthVoter::LOCAL_AUTH_ENABLED]);

    self::assertSame(VoterInterface::ACCESS_DENIED, $result);
  }

  #[Test]
  #[DataProvider('tokenProvider')]
  public function itDeniesWhenTheSettingHasNoValue(TokenInterface $token): void {
    $voter = new LocalAuthVoter($this->configurationWith(null));

    $result = $voter->vote($token, null, [LocalAuthVoter::LOCAL_AUTH_ENABLED]);

    self::assertSame(VoterInterface::ACCESS_DENIED, $result);
  }

  #[Test]
  #[DataProvider('tokenProvider')]
  public function itAbstainsOnAnyOtherAttribute(TokenInterface $token): void {
    $enabled = new LocalAuthVoter($this->configurationWith(true));
    $disabled = new LocalAuthVoter($this->configurationWith(false));

    self::assertSame(VoterInterface::ACCESS_ABSTAIN, $enabled->vote($token, null, ['GUEST_UPLOAD_ALLOWED']));
    self::assertSame(VoterInterface::ACCESS_ABSTAIN, $disabled->vote($token, null, ['GUEST_UPLOAD_ALLOWED']));
  }

  private function configurationWith(?bool $localAuthEnabled): ConfigurationProviderInterface {
    $configuration = $this->createStub(ConfigurationProviderInterface::class);
    $configuration->method('get')->willReturnMap([
      ['user.localAuthEnabled', $localAuthEnabled],
    ]);

    return $configuration;
  }

  /**
   * @param list<string> $roles
   */
  private static function tokenWithRoles(array $roles): TokenInterface {
    return new UsernamePasswordToken(new InMemoryUser('someone', null, $roles), 'test', $roles);
  }
}
