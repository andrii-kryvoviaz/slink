<?php

declare(strict_types=1);

namespace Unit\Slink\User\Application\Command\SsoAuthorize;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Slink\User\Application\Command\SsoAuthorize\SsoAuthorizeCommand;
use Slink\User\Application\Command\SsoAuthorize\SsoAuthorizeHandler;
use Slink\User\Domain\Contracts\OAuthAdapterInterface;
use Slink\User\Domain\Exception\InvalidCredentialsException;
use Slink\User\Domain\Repository\OAuthProviderRepositoryInterface;
use Slink\User\Domain\ValueObject\OAuth\ProviderSlug;
use Slink\User\Domain\ValueObject\OAuth\RedirectUri;
use Slink\User\Infrastructure\ReadModel\View\OAuthProviderView;

final class SsoAuthorizeHandlerTest extends TestCase {

  #[Test]
  public function itReturnsAuthUrlOnHappyPath(): void {
    $providerView = $this->createStub(OAuthProviderView::class);
    $providerView->method('isEnabled')->willReturn(true);

    $repository = $this->createMock(OAuthProviderRepositoryInterface::class);
    $repository->expects($this->once())
      ->method('findByProvider')
      ->with($this->equalTo(ProviderSlug::fromString('google')))
      ->willReturn($providerView);

    $expectedUrl = 'https://accounts.google.com/o/oauth2/v2/auth?client_id=xxx';

    $oauthAdapter = $this->createMock(OAuthAdapterInterface::class);
    $oauthAdapter->expects($this->once())
      ->method('getAuthorizationUrl')
      ->with(
        $providerView,
        $this->isInstanceOf(RedirectUri::class),
      )
      ->willReturn($expectedUrl);

    $handler = new SsoAuthorizeHandler($repository, $oauthAdapter, $this->createStub(LoggerInterface::class), 'http://localhost:3000');

    $command = new SsoAuthorizeCommand('google');

    $result = $handler($command);

    $this->assertSame($expectedUrl, $result);
  }

  #[Test]
  public function itThrowsWhenProviderNotFound(): void {
    $repository = $this->createStub(OAuthProviderRepositoryInterface::class);
    $repository->method('findByProvider')->willReturn(null);

    $oauthAdapter = $this->createMock(OAuthAdapterInterface::class);
    $oauthAdapter->expects($this->never())->method('getAuthorizationUrl');

    $handler = new SsoAuthorizeHandler($repository, $oauthAdapter, $this->createStub(LoggerInterface::class), 'http://localhost:3000');

    $command = new SsoAuthorizeCommand('google');

    $this->expectException(InvalidCredentialsException::class);

    $handler($command);
  }

  #[Test]
  public function itThrowsWhenProviderDisabled(): void {
    $providerView = $this->createStub(OAuthProviderView::class);
    $providerView->method('isEnabled')->willReturn(false);

    $repository = $this->createStub(OAuthProviderRepositoryInterface::class);
    $repository->method('findByProvider')->willReturn($providerView);

    $oauthAdapter = $this->createMock(OAuthAdapterInterface::class);
    $oauthAdapter->expects($this->never())->method('getAuthorizationUrl');

    $handler = new SsoAuthorizeHandler($repository, $oauthAdapter, $this->createStub(LoggerInterface::class), 'http://localhost:3000');

    $command = new SsoAuthorizeCommand('google');

    $this->expectException(InvalidCredentialsException::class);

    $handler($command);
  }
}
