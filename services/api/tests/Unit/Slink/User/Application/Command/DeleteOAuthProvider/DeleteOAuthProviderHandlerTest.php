<?php

declare(strict_types=1);

namespace Unit\Slink\User\Application\Command\DeleteOAuthProvider;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Application\Command\DeleteOAuthProvider\DeleteOAuthProviderCommand;
use Slink\User\Application\Command\DeleteOAuthProvider\DeleteOAuthProviderHandler;
use Slink\User\Domain\OAuthProvider;
use Slink\User\Domain\Repository\OAuthProviderStoreRepositoryInterface;

final class DeleteOAuthProviderHandlerTest extends TestCase {

  #[Test]
  public function itRemovesProvider(): void {
    $id = ID::generate();
    $provider = $this->createMock(OAuthProvider::class);

    $provider->expects($this->once())->method('remove');

    $providerStore = $this->createMock(OAuthProviderStoreRepositoryInterface::class);
    $providerStore->expects($this->once())
      ->method('get')
      ->with($this->callback(fn(ID $argId) => $argId->toString() === $id->toString()))
      ->willReturn($provider);
    $providerStore->expects($this->once())
      ->method('store')
      ->with($provider);

    $handler = new DeleteOAuthProviderHandler($providerStore);

    $command = new DeleteOAuthProviderCommand($id->toString());

    $handler($command);
  }
}
