<?php

declare(strict_types=1);

namespace Unit\Slink\User\Infrastructure\ReadModel\Projection;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\Enum\OAuthProvider;
use Slink\User\Domain\Event\OAuthProvider\OAuthProviderWasCreated;
use Slink\User\Domain\Event\OAuthProvider\OAuthProviderWasRemoved;
use Slink\User\Domain\Event\OAuthProvider\OAuthProviderWasUpdated;
use Slink\User\Domain\Repository\OAuthLinkRepositoryInterface;
use Slink\User\Domain\Repository\OAuthProviderRepositoryInterface;
use Slink\User\Infrastructure\ReadModel\Projection\OAuthProviderProjection;
use Slink\User\Infrastructure\ReadModel\View\OAuthProviderView;

final class OAuthProviderProjectionTest extends TestCase {

  #[Test]
  public function itCreatesViewOnProviderCreated(): void {
    $repository = $this->createMock(OAuthProviderRepositoryInterface::class);
    $repository->expects($this->once())
      ->method('save')
      ->with($this->isInstanceOf(OAuthProviderView::class));

    $linkRepository = $this->createStub(OAuthLinkRepositoryInterface::class);

    $projection = new OAuthProviderProjection($repository, $linkRepository);

    $event = new OAuthProviderWasCreated(
      id: ID::generate()->toString(),
      name: 'Google',
      slug: 'google',
      type: 'oidc',
      clientId: 'client-id-123',
      clientSecret: 'client-secret-456',
      discoveryUrl: 'https://accounts.google.com/.well-known/openid-configuration',
      scopes: 'openid email profile',
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
      id: $id->toString(),
      name: 'Updated Google',
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
      id: $id->toString(),
      name: 'Updated Google',
    );

    $projection->handleOAuthProviderWasUpdated($event);
  }

  #[Test]
  public function itDeletesViewOnProviderRemoved(): void {
    $id = ID::generate();
    $providerView = $this->createStub(OAuthProviderView::class);
    $providerView->method('getSlug')->willReturn('google');

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
