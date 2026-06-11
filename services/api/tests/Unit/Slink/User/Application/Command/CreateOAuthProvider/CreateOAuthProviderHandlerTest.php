<?php

declare(strict_types=1);

namespace Unit\Slink\User\Application\Command\CreateOAuthProvider;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\User\Application\Command\CreateOAuthProvider\CreateOAuthProviderCommand;
use Slink\User\Application\Command\CreateOAuthProvider\CreateOAuthProviderHandler;
use Slink\User\Domain\Enum\ApprovalPolicy;
use Slink\User\Domain\Enum\RegistrationPolicy;
use Slink\User\Domain\Event\OAuthProvider\OAuthProviderWasCreated;
use Slink\User\Domain\Exception\DuplicateOAuthProviderException;
use Slink\User\Domain\OAuthProvider;
use Slink\User\Domain\Repository\OAuthProviderRepositoryInterface;
use Slink\User\Domain\Repository\OAuthProviderStoreRepositoryInterface;
use Slink\User\Domain\Specification\UniqueOAuthProviderSpecificationInterface;

final class CreateOAuthProviderHandlerTest extends TestCase {

  #[Test]
  public function itCreatesProviderOnHappyPath(): void {
    $uniqueSpec = $this->createMock(UniqueOAuthProviderSpecificationInterface::class);
    $uniqueSpec->expects($this->once())
      ->method('ensureUnique');

    $repository = $this->createStub(OAuthProviderRepositoryInterface::class);
    $repository->method('getMaxSortOrder')->willReturn(2.0);

    $providerStore = $this->createMock(OAuthProviderStoreRepositoryInterface::class);
    $providerStore->expects($this->once())
      ->method('store')
      ->with($this->isInstanceOf(OAuthProvider::class));

    $handler = new CreateOAuthProviderHandler($providerStore, $uniqueSpec, $repository);

    $command = new CreateOAuthProviderCommand(
      name: 'Google',
      slug: 'google',
      clientId: 'client-id-123',
      clientSecret: 'client-secret-456',
      discoveryUrl: 'https://accounts.google.com/.well-known/openid-configuration',
      type: 'oidc',
      scopes: 'openid email profile',
      enabled: true,
    );

    $result = $handler($command);

    $this->assertNotEmpty($result);
  }

  #[Test]
  public function itPersistsProviderPolicies(): void {
    $uniqueSpec = $this->createStub(UniqueOAuthProviderSpecificationInterface::class);

    $repository = $this->createStub(OAuthProviderRepositoryInterface::class);
    $repository->method('getMaxSortOrder')->willReturn(0.0);

    $providerStore = $this->createMock(OAuthProviderStoreRepositoryInterface::class);
    $providerStore->expects($this->once())
      ->method('store')
      ->with($this->callback(function (OAuthProvider $provider): bool {
        $events = $provider->releaseEvents();
        $created = $events[0] ?? null;

        return $created instanceof OAuthProviderWasCreated
          && $created->registrationPolicy === RegistrationPolicy::Allowed
          && $created->approvalPolicy === ApprovalPolicy::Required;
      }));

    $handler = new CreateOAuthProviderHandler($providerStore, $uniqueSpec, $repository);

    $command = new CreateOAuthProviderCommand(
      name: 'Google',
      slug: 'google',
      clientId: 'client-id-123',
      clientSecret: 'client-secret-456',
      discoveryUrl: 'https://accounts.google.com/.well-known/openid-configuration',
      registrationPolicy: 'allowed',
      approvalPolicy: 'required',
    );

    $handler($command);
  }

  #[Test]
  public function itDefaultsPoliciesToInherit(): void {
    $uniqueSpec = $this->createStub(UniqueOAuthProviderSpecificationInterface::class);

    $repository = $this->createStub(OAuthProviderRepositoryInterface::class);
    $repository->method('getMaxSortOrder')->willReturn(0.0);

    $providerStore = $this->createMock(OAuthProviderStoreRepositoryInterface::class);
    $providerStore->expects($this->once())
      ->method('store')
      ->with($this->callback(function (OAuthProvider $provider): bool {
        $events = $provider->releaseEvents();
        $created = $events[0] ?? null;

        return $created instanceof OAuthProviderWasCreated
          && $created->registrationPolicy === RegistrationPolicy::Inherit
          && $created->approvalPolicy === ApprovalPolicy::Inherit;
      }));

    $handler = new CreateOAuthProviderHandler($providerStore, $uniqueSpec, $repository);

    $command = new CreateOAuthProviderCommand(
      name: 'Google',
      slug: 'google',
      clientId: 'client-id-123',
      clientSecret: 'client-secret-456',
      discoveryUrl: 'https://accounts.google.com/.well-known/openid-configuration',
    );

    $handler($command);
  }

  #[Test]
  public function itThrowsOnDuplicateSlug(): void {
    $uniqueSpec = $this->createMock(UniqueOAuthProviderSpecificationInterface::class);
    $uniqueSpec->expects($this->once())
      ->method('ensureUnique')
      ->willThrowException(new DuplicateOAuthProviderException('google'));

    $repository = $this->createStub(OAuthProviderRepositoryInterface::class);
    $providerStore = $this->createMock(OAuthProviderStoreRepositoryInterface::class);
    $providerStore->expects($this->never())->method('store');

    $handler = new CreateOAuthProviderHandler($providerStore, $uniqueSpec, $repository);

    $command = new CreateOAuthProviderCommand(
      name: 'Google',
      slug: 'google',
      clientId: 'client-id-123',
      clientSecret: 'client-secret-456',
      discoveryUrl: 'https://accounts.google.com/.well-known/openid-configuration',
    );

    $this->expectException(DuplicateOAuthProviderException::class);

    $handler($command);
  }
}
