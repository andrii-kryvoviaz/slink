<?php

declare(strict_types=1);

namespace Unit\Slink\User\Infrastructure\Auth;

use PHPUnit\Framework\TestCase;
use Slink\User\Infrastructure\Auth\ApiKeyAuthenticator;
use Slink\User\Infrastructure\Auth\ApiKeyUser;
use Slink\User\Infrastructure\Auth\ApiKeyUserProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

final class ApiKeyAuthenticatorTest extends TestCase {
  private ApiKeyUserProvider $userProvider;
  private ApiKeyAuthenticator $authenticator;

  protected function setUp(): void {
    $this->userProvider = $this->createStub(ApiKeyUserProvider::class);
    $this->authenticator = new ApiKeyAuthenticator($this->userProvider);
  }

  public function testItSupportsValidApiKeyRequest(): void {
    $request = new Request();
    $request->headers->set('Authorization', 'Bearer sk_test_key_12345');
    
    $this->assertTrue($this->authenticator->supports($request));
  }

  public function testItDoesNotSupportRequestWithoutAuthHeader(): void {
    $request = new Request();
    
    $this->assertFalse($this->authenticator->supports($request));
  }

  public function testItDoesNotSupportRequestWithInvalidKeyFormat(): void {
    $request = new Request();
    $request->headers->set('Authorization', 'Bearer invalid_key');
    
    $this->assertFalse($this->authenticator->supports($request));
  }

  public function testItAuthenticatesValidApiKey(): void {
    $request = new Request();
    $request->headers->set('Authorization', 'Bearer sk_test_key_12345');
    
    $passport = $this->authenticator->authenticate($request);
    
    $this->assertInstanceOf(Passport::class, $passport);
  }

  public function testItThrowsExceptionWhenNoAuthHeader(): void {
    $this->expectException(CustomUserMessageAuthenticationException::class);
    $this->expectExceptionMessage('No API key provided');
    
    $request = new Request();
    
    $this->authenticator->authenticate($request);
  }

  public function testItThrowsExceptionForInvalidApiKeyFormat(): void {
    $this->expectException(CustomUserMessageAuthenticationException::class);
    $this->expectExceptionMessage('Invalid API key format');
    
    $request = new Request();
    $request->headers->set('Authorization', 'Bearer invalid_key');
    
    $this->authenticator->authenticate($request);
  }

  public function testItReturnsNullOnAuthenticationSuccess(): void {
    $request = new Request();
    $token = $this->createStub(TokenInterface::class);
    
    $result = $this->authenticator->onAuthenticationSuccess($request, $token, 'api');
    
    $this->assertNull($result);
  }

  public function testItReturnsJsonResponseOnAuthenticationFailure(): void {
    $request = new Request();
    $exception = new AuthenticationException('Test failure');
    
    $response = $this->authenticator->onAuthenticationFailure($request, $exception);
    
    $this->assertEquals(401, $response->getStatusCode());
    $content = $response->getContent();
    $this->assertIsString($content);
    $this->assertStringContainsString('Authentication failed', $content);
    $this->assertStringContainsString('INVALID_API_KEY', $content);
  }
}
