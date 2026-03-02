<?php

declare(strict_types=1);

namespace Unit\Slink\User\Application\Command\UpdateOAuthProvider;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Application\Command\UpdateOAuthProvider\UpdateOAuthProviderCommand;
use Slink\User\Application\Command\UpdateOAuthProvider\UpdateOAuthProviderHandler;
use Slink\User\Domain\OAuthProvider;
use Slink\User\Domain\Repository\OAuthProviderStoreRepositoryInterface;

final class UpdateOAuthProviderHandlerTest extends TestCase {

  #[Test]
  public function itUpdatesProvider(): void {
    $id = ID::generate();
    $provider = $this->createMock(OAuthProvider::class);

    $provider->expects($this->once())
      ->method('update')
      ->with(
        name: 'Updated Google',
        slug: 'google',
        type: 'oidc',
        clientId: 'new-client-id',
        clientSecret: 'new-client-secret',
        discoveryUrl: 'https://accounts.google.com/.well-known/openid-configuration',
        scopes: 'openid email',
        enabled: true,
        sortOrder: 2.0,
      );

    $providerStore = $this->createMock(OAuthProviderStoreRepositoryInterface::class);
    $providerStore->expects($this->once())
      ->method('get')
      ->with($this->callback(fn(ID $argId) => $argId->toString() === $id->toString()))
      ->willReturn($provider);
    $providerStore->expects($this->once())
      ->method('store')
      ->with($provider);

    $handler = new UpdateOAuthProviderHandler($providerStore);

    $command = new UpdateOAuthProviderCommand(
      name: 'Updated Google',
      slug: 'google',
      type: 'oidc',
      clientId: 'new-client-id',
      clientSecret: 'new-client-secret',
      discoveryUrl: 'https://accounts.google.com/.well-known/openid-configuration',
      scopes: 'openid email',
      enabled: true,
      sortOrder: 2.0,
    );

    $handler($command, $id->toString());
  }
}
