<?php

declare(strict_types=1);

namespace Unit\Slink\User\Infrastructure\ReadModel\Projection;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Encryption\EncryptionRegistry;
use Slink\Shared\Infrastructure\Encryption\EncryptionService;
use Slink\User\Domain\Enum\ApprovalPolicy;
use Slink\User\Domain\Enum\RegistrationPolicy;
use Slink\User\Domain\Event\OAuthProvider\OAuthProviderWasCreated;
use Slink\User\Domain\ValueObject\OAuth\ProviderSlug;
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
      slug: ProviderSlug::fromString('google'),
      type: OAuthType::fromString('oidc'),
      clientId: ClientId::fromString('client-id-123'),
      clientSecret: ClientSecret::fromString('client-secret-456'),
      discoveryUrl: DiscoveryUrl::fromString('https://accounts.google.com/.well-known/openid-configuration'),
      scopes: OAuthScopes::fromString('openid email profile'),
      registrationPolicy: RegistrationPolicy::Inherit,
      approvalPolicy: ApprovalPolicy::Inherit,
      enabled: true,
      sortOrder: 1.0,
    );

    $projection->handleOAuthProviderWasCreated($event);
  }

  #[Test]
  public function itUpdatesAllFieldsOnProviderUpdated(): void {
    $id = ID::generate();
    $providerView = $this->createProviderView($id->toString());

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
      slug: ProviderSlug::fromString('updated-google'),
      type: OAuthType::fromString('oidc'),
      clientId: ClientId::fromString('new-client-id'),
      clientSecret: ClientSecret::fromString('new-client-secret'),
      discoveryUrl: DiscoveryUrl::fromString('https://login.example.com/.well-known/openid-configuration'),
      scopes: OAuthScopes::fromString('openid profile'),
      registrationPolicy: RegistrationPolicy::Allowed,
      approvalPolicy: ApprovalPolicy::Required,
      enabled: false,
      sortOrder: 5.0,
    );

    $projection->handleOAuthProviderWasUpdated($event);

    $this->assertSame('Updated Google', $providerView->getName());
    $this->assertSame('updated-google', $providerView->getSlug()->toString());
    $this->assertSame('oidc', $providerView->getType());
    $this->assertSame('new-client-id', $providerView->getClientId()->toString());
    $this->assertSame('new-client-secret', $providerView->getClientSecret()->toString());
    $this->assertSame('https://login.example.com/.well-known/openid-configuration', $providerView->getDiscoveryUrl()->toString());
    $this->assertSame('openid profile', $providerView->getScopes()->toString());
    $this->assertFalse($providerView->isEnabled());
    $this->assertSame(5.0, $providerView->getSortOrder());
    $this->assertSame(RegistrationPolicy::Allowed, $providerView->getRegistrationPolicy());
    $this->assertSame(ApprovalPolicy::Required, $providerView->getApprovalPolicy());
  }

  #[Test]
  public function itKeepsUnchangedFieldsOnPartialUpdate(): void {
    $id = ID::generate();
    $providerView = $this->createProviderView($id->toString());

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

    $this->assertSame('Updated Google', $providerView->getName());
    $this->assertFalse($providerView->isEnabled());
    $this->assertSame('google', $providerView->getSlug()->toString());
    $this->assertSame('oidc', $providerView->getType());
    $this->assertSame('client-id-123', $providerView->getClientId()->toString());
    $this->assertSame('client-secret-456', $providerView->getClientSecret()->toString());
    $this->assertSame('https://accounts.google.com/.well-known/openid-configuration', $providerView->getDiscoveryUrl()->toString());
    $this->assertSame('openid email profile', $providerView->getScopes()->toString());
    $this->assertSame(1.0, $providerView->getSortOrder());
    $this->assertSame(RegistrationPolicy::Inherit, $providerView->getRegistrationPolicy());
    $this->assertSame(ApprovalPolicy::Inherit, $providerView->getApprovalPolicy());
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
    $providerView->method('getSlug')->willReturn(ProviderSlug::fromString('google'));

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
      ->with(ProviderSlug::fromString('google'));

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

  private function createProviderView(string $id): OAuthProviderView {
    return new OAuthProviderView(
      id: $id,
      name: 'Google',
      slug: 'google',
      type: 'oidc',
      clientId: 'client-id-123',
      clientSecret: 'client-secret-456',
      discoveryUrl: 'https://accounts.google.com/.well-known/openid-configuration',
      scopes: 'openid email profile',
      enabled: true,
      sortOrder: 1.0,
      registrationPolicy: 'inherit',
      approvalPolicy: 'inherit',
    );
  }
}
