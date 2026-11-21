<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\User\Domain;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Domain\ValueObject\AbstractStringValueObject;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Encryption\EncryptionRegistry;
use Slink\Shared\Infrastructure\Encryption\EncryptionService;
use Slink\User\Domain\Enum\ApprovalPolicy;
use Slink\User\Domain\Enum\RegistrationPolicy;
use Slink\User\Domain\Event\OAuthProvider\OAuthProviderWasRemoved;
use Slink\User\Domain\Event\OAuthProvider\OAuthProviderWasUpdated;
use Slink\User\Domain\OAuthProvider;
use Slink\User\Domain\ValueObject\OAuth\ClientId;
use Slink\User\Domain\ValueObject\OAuth\ClientSecret;
use Slink\User\Domain\ValueObject\OAuth\DiscoveryUrl;
use Slink\User\Domain\ValueObject\OAuth\OAuthScopes;
use Slink\User\Domain\ValueObject\OAuth\ProviderName;
use Slink\User\Domain\ValueObject\OAuth\ProviderSlug;
use Slink\User\Infrastructure\ReadModel\View\OAuthProviderView;

#[Group('sc-1176')]
final class OAuthProviderTest extends TestCase {
  private const string DISCOVERY_URL = 'https://accounts.google.com/.well-known/openid-configuration';

  protected function setUp(): void {
    EncryptionRegistry::setService(new EncryptionService('test-encryption-secret'));
  }

  #[Test]
  public function itHydratesEveryFieldFromItsView(): void {
    $id = ID::generate();

    $provider = OAuthProvider::fromView($this->createView($id));

    $this->assertTrue($id->equals($provider->aggregateRootId()));
    $this->assertSame('Google', $this->stringState($provider, 'name'));
    $this->assertSame('google', $this->stringState($provider, 'slug'));
    $this->assertSame('oidc', $this->stringState($provider, 'type'));
    $this->assertSame('plain-client-id', $this->stringState($provider, 'clientId'));
    $this->assertSame('plain-client-secret', $this->stringState($provider, 'clientSecret'));
    $this->assertSame(self::DISCOVERY_URL, $this->stringState($provider, 'discoveryUrl'));
    $this->assertSame('openid profile', $this->stringState($provider, 'scopes'));
    $this->assertFalse($this->state($provider, 'enabled'));
    $this->assertSame(2.5, $this->state($provider, 'sortOrder'));
    $this->assertSame(RegistrationPolicy::Blocked, $this->state($provider, 'registrationPolicy'));
    $this->assertSame(ApprovalPolicy::Required, $this->state($provider, 'approvalPolicy'));
  }

  #[Test]
  public function itRecordsNoEventsWhenHydrated(): void {
    $this->assertSame([], OAuthProvider::fromView($this->createView(ID::generate()))->releaseEvents());
  }

  #[Test]
  public function itUpdatesAHydratedProviderKeepingItsCredentials(): void {
    $provider = OAuthProvider::fromView($this->createView(ID::generate()));

    $provider->update(name: ProviderName::fromString('Renamed'));

    $this->assertSame('Renamed', $this->stringState($provider, 'name'));
    $this->assertSame('plain-client-id', $this->stringState($provider, 'clientId'));
    $this->assertSame('plain-client-secret', $this->stringState($provider, 'clientSecret'));
    $events = $provider->releaseEvents();
    $this->assertCount(1, $events);
    $this->assertInstanceOf(OAuthProviderWasUpdated::class, $events[0]);
  }

  #[Test]
  public function itRemovesAHydratedProvider(): void {
    $id = ID::generate();
    $provider = OAuthProvider::fromView($this->createView($id));

    $provider->remove();

    $events = $provider->releaseEvents();
    $this->assertCount(1, $events);
    $this->assertInstanceOf(OAuthProviderWasRemoved::class, $events[0]);
    $this->assertSame($id->toString(), $events[0]->id);
  }

  private function createView(ID $id): OAuthProviderView {
    $view = $this->createStub(OAuthProviderView::class);
    $view->method('getId')->willReturn($id->toString());
    $view->method('getName')->willReturn('Google');
    $view->method('getSlug')->willReturn(ProviderSlug::fromString('google'));
    $view->method('getType')->willReturn('oidc');
    $view->method('getClientId')->willReturn(ClientId::fromString('plain-client-id'));
    $view->method('getClientSecret')->willReturn(ClientSecret::fromString('plain-client-secret'));
    $view->method('getDiscoveryUrl')->willReturn(DiscoveryUrl::fromString(self::DISCOVERY_URL));
    $view->method('getScopes')->willReturn(OAuthScopes::fromString('openid profile'));
    $view->method('isEnabled')->willReturn(false);
    $view->method('getSortOrder')->willReturn(2.5);
    $view->method('getRegistrationPolicy')->willReturn(RegistrationPolicy::Blocked);
    $view->method('getApprovalPolicy')->willReturn(ApprovalPolicy::Required);

    return $view;
  }

  private function stringState(OAuthProvider $provider, string $property): string {
    $value = $this->state($provider, $property);
    $this->assertInstanceOf(AbstractStringValueObject::class, $value);

    return $value->toString();
  }

  private function state(OAuthProvider $provider, string $property): mixed {
    return new \ReflectionProperty(OAuthProvider::class, $property)->getValue($provider);
  }
}
