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
  public function itGrantsUploadAccessForAuthenticatedUsers(): void {
    $configProvider = $this->createMock(ConfigurationProviderInterface::class);
    $configProvider->expects($this->never())->method('get');
    
    $user = $this->createMock(UserInterface::class);
    $token = $this->createMock(TokenInterface::class);
    $token->method('getUser')->willReturn($user);
    
    $voter = new GuestAccessVoter($configProvider);
    
    $result = $voter->vote($token, null, [GuestAccessVoter::GUEST_UPLOAD_ALLOWED]);
    
    $this->assertEquals(GuestAccessVoter::ACCESS_GRANTED, $result);
  }
  
  #[Test]
  public function itGrantsViewAccessForAuthenticatedUsers(): void {
    $configProvider = $this->createMock(ConfigurationProviderInterface::class);
    $configProvider->expects($this->never())->method('get');
    
    $user = $this->createMock(UserInterface::class);
    $token = $this->createMock(TokenInterface::class);
    $token->method('getUser')->willReturn($user);
    
    $voter = new GuestAccessVoter($configProvider);
    
    $result = $voter->vote($token, null, [GuestAccessVoter::GUEST_VIEW_ALLOWED]);
    
    $this->assertEquals(GuestAccessVoter::ACCESS_GRANTED, $result);
  }
  
  #[Test]
  public function itGrantsUploadAccessForGuestUploadsWhenEnabled(): void {
    $configProvider = $this->createMock(ConfigurationProviderInterface::class);
    $configProvider->method('get')
      ->with('access.allowGuestUploads')
      ->willReturn(true);
    
    $token = $this->createMock(TokenInterface::class);
    $token->method('getUser')->willReturn(null);
    
    $voter = new GuestAccessVoter($configProvider);
    
    $result = $voter->vote($token, null, [GuestAccessVoter::GUEST_UPLOAD_ALLOWED]);
    
    $this->assertEquals(GuestAccessVoter::ACCESS_GRANTED, $result);
  }
  
  #[Test]
  public function itDeniesUploadAccessForGuestUploadsWhenDisabled(): void {
    $configProvider = $this->createMock(ConfigurationProviderInterface::class);
    $configProvider->method('get')
      ->with('access.allowGuestUploads')
      ->willReturn(false);
    
    $token = $this->createMock(TokenInterface::class);
    $token->method('getUser')->willReturn(null);
    
    $voter = new GuestAccessVoter($configProvider);
    
    $result = $voter->vote($token, null, [GuestAccessVoter::GUEST_UPLOAD_ALLOWED]);
    
    $this->assertEquals(GuestAccessVoter::ACCESS_DENIED, $result);
  }
  
  #[Test]
  public function itGrantsViewAccessForGuestViewingWhenEnabled(): void {
    $configProvider = $this->createMock(ConfigurationProviderInterface::class);
    $configProvider->method('get')
      ->with('access.allowUnauthenticatedAccess')
      ->willReturn(true);
    
    $token = $this->createMock(TokenInterface::class);
    $token->method('getUser')->willReturn(null);
    
    $voter = new GuestAccessVoter($configProvider);
    
    $result = $voter->vote($token, null, [GuestAccessVoter::GUEST_VIEW_ALLOWED]);
    
    $this->assertEquals(GuestAccessVoter::ACCESS_GRANTED, $result);
  }
  
  #[Test]
  public function itDeniesViewAccessForGuestViewingWhenDisabled(): void {
    $configProvider = $this->createMock(ConfigurationProviderInterface::class);
    $configProvider->method('get')
      ->with('access.allowUnauthenticatedAccess')
      ->willReturn(false);
    
    $token = $this->createMock(TokenInterface::class);
    $token->method('getUser')->willReturn(null);
    
    $voter = new GuestAccessVoter($configProvider);
    
    $result = $voter->vote($token, null, [GuestAccessVoter::GUEST_VIEW_ALLOWED]);
    
    $this->assertEquals(GuestAccessVoter::ACCESS_DENIED, $result);
  }
  
  #[Test]
  public function itHandlesBothAttributesIndependently(): void {
    $configProvider = $this->createMock(ConfigurationProviderInterface::class);
    $configProvider->method('get')
      ->willReturnMap([
        ['access.allowGuestUploads', true],
        ['access.allowUnauthenticatedAccess', true],
      ]);
    
    $token = $this->createMock(TokenInterface::class);
    $token->method('getUser')->willReturn(null);
    
    $voter = new GuestAccessVoter($configProvider);
    
    $uploadResult = $voter->vote($token, null, [GuestAccessVoter::GUEST_UPLOAD_ALLOWED]);
    $viewResult = $voter->vote($token, null, [GuestAccessVoter::GUEST_VIEW_ALLOWED]);
    
    $this->assertEquals(GuestAccessVoter::ACCESS_GRANTED, $uploadResult);
    $this->assertEquals(GuestAccessVoter::ACCESS_GRANTED, $viewResult);
  }
  
  #[Test]
  public function itDeniesViewAccessWhenOnlyGuestUploadsIsEnabled(): void {
    $configProvider = $this->createMock(ConfigurationProviderInterface::class);
    $configProvider->method('get')
      ->with('access.allowUnauthenticatedAccess')
      ->willReturn(false);
    
    $token = $this->createMock(TokenInterface::class);
    $token->method('getUser')->willReturn(null);
    
    $voter = new GuestAccessVoter($configProvider);
    
    $result = $voter->vote($token, null, [GuestAccessVoter::GUEST_VIEW_ALLOWED]);
    
    $this->assertEquals(GuestAccessVoter::ACCESS_DENIED, $result);
  }
  
  #[Test]
  public function itDeniesUploadAccessWhenOnlyGuestViewingIsEnabled(): void {
    $configProvider = $this->createMock(ConfigurationProviderInterface::class);
    $configProvider->method('get')
      ->with('access.allowGuestUploads')
      ->willReturn(false);
    
    $token = $this->createMock(TokenInterface::class);
    $token->method('getUser')->willReturn(null);
    
    $voter = new GuestAccessVoter($configProvider);
    
    $result = $voter->vote($token, null, [GuestAccessVoter::GUEST_UPLOAD_ALLOWED]);
    
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
  
  #[Test]
  public function itSupportsGuestUploadAllowedAttribute(): void {
    $configProvider = $this->createMock(ConfigurationProviderInterface::class);
    $configProvider->method('get')
      ->with('access.allowGuestUploads')
      ->willReturn(false);
      
    $voter = new GuestAccessVoter($configProvider);
    $token = $this->createMock(TokenInterface::class);
    $token->method('getUser')->willReturn(null);
    
    $result = $voter->vote($token, null, [GuestAccessVoter::GUEST_UPLOAD_ALLOWED]);
    
    $this->assertNotEquals(GuestAccessVoter::ACCESS_ABSTAIN, $result);
  }
  
  #[Test]
  public function itSupportsGuestViewAllowedAttribute(): void {
    $configProvider = $this->createMock(ConfigurationProviderInterface::class);
    $configProvider->method('get')
      ->with('access.allowUnauthenticatedAccess')
      ->willReturn(false);
      
    $voter = new GuestAccessVoter($configProvider);
    $token = $this->createMock(TokenInterface::class);
    $token->method('getUser')->willReturn(null);
    
    $result = $voter->vote($token, null, [GuestAccessVoter::GUEST_VIEW_ALLOWED]);
    
    $this->assertNotEquals(GuestAccessVoter::ACCESS_ABSTAIN, $result);
  }
}
