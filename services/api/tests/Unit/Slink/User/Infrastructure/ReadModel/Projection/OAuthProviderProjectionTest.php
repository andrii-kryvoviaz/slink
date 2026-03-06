<?php

declare(strict_types=1);

namespace Unit\Slink\User\Infrastructure\ReadModel\Projection;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Encryption\EncryptionRegistry;
use Slink\Shared\Infrastructure\Encryption\EncryptionService;
use Slink\User\Domain\Enum\OAuthProvider;
use Slink\User\Domain\Event\OAuthProvider\OAuthProviderWasCreated;
use Slink\User\Domain\Event\OAuthProvider\OAuthProviderWasRemoved;
use Slink\User\Domain\Event\OAuthProvider\OAuthProviderWasUpdated;
use Slink\User\Domain\Repository\OAuthLinkRepositoryInterface;
use Slink\User\Domain\Repository\OAuthProviderRepositoryInterface;
use Slink\User\Domain\ValueObject\OAuth\ClientId;
use Slink\User\Domain\ValueObject\OAuth\ClientSecret;
use Slink\User\Domain\ValueObject\OAuth\DiscoveryUrl;
use Slink\User\Domain\ValueObject\OAuth\OAuthScopes;
use Slink\User\Domain\ValueObject\OAuth\OAuthType;
use Slink\User\Domain\ValueObject\OAuth\ProviderName;
use Slink\User\Infrastructure\ReadModel\Projection\OAuthProviderProjection;
use Slink\User\Infrastructure\ReadModel\View\OAuthProviderView;

final class OAuthProviderProjectionTest extends TestCase {
  protected function setUp(): void {
    EncryptionRegistry::setService(new EncryptionService('test-secret'));
  }

  #[Test]
  public function itCreatesViewOnProviderCreated(): void {
    $repository = $this->createMock(OAuthProviderRepositoryInterface::class);
    $repository->expects($this->once())
      ->method('save')
      ->with($this->isInstanceOf(OAuthProviderView::class));

    $linkRepository = $this->createStub(OAuthLinkRepositoryInterface::class);

    $projection = new OAuthProviderProjection($repository, $linkRepository);

    $event = new OAuthProviderWasCreated(
      id: ID::generate(),
      name: ProviderName::fromString('Google'),
      slug: OAuthProvider::Google,
      type: OAuthType::fromString('oidc'),
      clientId: ClientId::fromString('client-id-123'),
      clientSecret: ClientSecret::fromString('client-secret-456'),
      discoveryUrl: DiscoveryUrl::fromString('https://accounts.google.com/.well-known/openid-configuration'),
      scopes: OAuthScopes::fromString('openid email profile'),
      enabled: true,
      sortOrder: 1.0,
    );

    $projection->handleOAuthProviderWasCreated($event);
  }

  #[Test]
  public function itUpdatesViewOnProviderUpdated(): void {
    $id = ID::generate();

    $providerView = $this->createMock(OAuthProviderView::class);
    $providerView->expects($this->once())->method('setName')->with('Updated Google');
    $providerView->expects($this->once())->method('setEnabled')->with(false);

    $repository = $this->createMock(OAuthProviderRepositoryInterface::class);
    $repository->expects($this->once())
      ->method('findById')
      ->with($this->callback(fn(ID $argId) => $argId->toString() === $id->toString()))
      ->willReturn($providerView);
    $repository->expects($this->once())
      ->method('save')
      ->with($providerView);

    $linkRepository = $this->createStub(OAuthLinkRepositoryInterface::class);

    $projection = new OAuthProviderProjection($repository, $linkRepository);

    $event = new OAuthProviderWasUpdated(
      id: $id,
      name: ProviderName::fromString('Updated Google'),
      enabled: false,
    );

    $projection->handleOAuthProviderWasUpdated($event);
  }

  #[Test]
  public function itReturnsEarlyOnUpdateWithNullProvider(): void {
    $id = ID::generate();

    $repository = $this->createMock(OAuthProviderRepositoryInterface::class);
    $repository->expects($this->once())
      ->method('findById')
      ->willReturn(null);
    $repository->expects($this->never())
      ->method('save');

    $linkRepository = $this->createStub(OAuthLinkRepositoryInterface::class);

    $projection = new OAuthProviderProjection($repository, $linkRepository);

    $event = new OAuthProviderWasUpdated(
      id: $id,
      name: ProviderName::fromString('Updated Google'),
    );

    $projection->handleOAuthProviderWasUpdated($event);
  }

  #[Test]
  public function itDeletesViewOnProviderRemoved(): void {
    $id = ID::generate();
    $providerView = $this->createStub(OAuthProviderView::class);
    $providerView->method('getSlug')->willReturn(OAuthProvider::Google);

    $repository = $this->createMock(OAuthProviderRepositoryInterface::class);
    $repository->expects($this->once())
      ->method('findById')
      ->with($this->callback(fn(ID $argId) => $argId->toString() === $id->toString()))
      ->willReturn($providerView);
    $repository->expects($this->once())
      ->method('delete')
      ->with($providerView);

    $linkRepository = $this->createMock(OAuthLinkRepositoryInterface::class);
    $linkRepository->expects($this->once())
      ->method('deleteByProviderSlug')
      ->with(OAuthProvider::Google);

    $projection = new OAuthProviderProjection($repository, $linkRepository);

    $event = new OAuthProviderWasRemoved(id: $id->toString());

    $projection->handleOAuthProviderWasRemoved($event);
  }

  #[Test]
  public function itHandlesRemoveOfNonexistentProvider(): void {
    $id = ID::generate();

    $repository = $this->createMock(OAuthProviderRepositoryInterface::class);
    $repository->expects($this->once())
      ->method('findById')
      ->willReturn(null);
    $repository->expects($this->never())
      ->method('delete');

    $linkRepository = $this->createMock(OAuthLinkRepositoryInterface::class);
    $linkRepository->expects($this->never())
      ->method('deleteByProviderSlug');

    $projection = new OAuthProviderProjection($repository, $linkRepository);

    $event = new OAuthProviderWasRemoved(id: $id->toString());

    $projection->handleOAuthProviderWasRemoved($event);
  }
}
