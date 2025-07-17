<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Shared\Infrastructure\Security\Voter;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Infrastructure\Security\Voter\GuestAccessVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class GuestAccessVoterTest extends TestCase {
  
  #[Test]
  public function itGrantsAccessForAuthenticatedUsers(): void {
    $configProvider = $this->createMock(ConfigurationProviderInterface::class);
    $configProvider->expects($this->never())->method('get');
    
    $user = $this->createMock(UserInterface::class);
    $token = $this->createMock(TokenInterface::class);
    $token->method('getUser')->willReturn($user);
    
    $voter = new GuestAccessVoter($configProvider);
    
    $result = $voter->vote($token, null, [GuestAccessVoter::GUEST_ACCESS_ALLOWED]);
    
    $this->assertEquals(GuestAccessVoter::ACCESS_GRANTED, $result);
  }
  
  #[Test]
  public function itGrantsAccessWhenGuestUploadsAreAllowed(): void {
    $configProvider = $this->createMock(ConfigurationProviderInterface::class);
    $configProvider->method('get')
      ->willReturnMap([
        ['access.allowGuestUploads', true],
        ['access.allowUnauthenticatedAccess', false],
      ]);
    
    $token = $this->createMock(TokenInterface::class);
    $token->method('getUser')->willReturn(null);
    
    $voter = new GuestAccessVoter($configProvider);
    
    $result = $voter->vote($token, null, [GuestAccessVoter::GUEST_ACCESS_ALLOWED]);
    
    $this->assertEquals(GuestAccessVoter::ACCESS_GRANTED, $result);
  }
  
  #[Test]
  public function itGrantsAccessWhenUnauthenticatedAccessIsAllowed(): void {
    $configProvider = $this->createMock(ConfigurationProviderInterface::class);
    $configProvider->method('get')
      ->willReturnMap([
        ['access.allowGuestUploads', false],
        ['access.allowUnauthenticatedAccess', true],
      ]);
    
    $token = $this->createMock(TokenInterface::class);
    $token->method('getUser')->willReturn(null);
    
    $voter = new GuestAccessVoter($configProvider);
    
    $result = $voter->vote($token, null, [GuestAccessVoter::GUEST_ACCESS_ALLOWED]);
    
    $this->assertEquals(GuestAccessVoter::ACCESS_GRANTED, $result);
  }
  
  #[Test]
  public function itDeniesAccessForUnauthenticatedUsersWhenBothSettingsAreDisabled(): void {
    $configProvider = $this->createMock(ConfigurationProviderInterface::class);
    $configProvider->method('get')
      ->willReturnMap([
        ['access.allowGuestUploads', false],
        ['access.allowUnauthenticatedAccess', false],
      ]);
    
    $token = $this->createMock(TokenInterface::class);
    $token->method('getUser')->willReturn(null);
    
    $voter = new GuestAccessVoter($configProvider);
    
    $result = $voter->vote($token, null, [GuestAccessVoter::GUEST_ACCESS_ALLOWED]);
    
    $this->assertEquals(GuestAccessVoter::ACCESS_DENIED, $result);
  }
  
  #[Test]
  public function itAbstainsForUnsupportedAttribute(): void {
    $configProvider = $this->createMock(ConfigurationProviderInterface::class);
    
    $voter = new GuestAccessVoter($configProvider);
    $token = $this->createMock(TokenInterface::class);
    
    $result = $voter->vote($token, null, ['UNSUPPORTED_ATTRIBUTE']);
    
    $this->assertEquals(GuestAccessVoter::ACCESS_ABSTAIN, $result);
  }
}
