<?php

declare(strict_types=1);

namespace Unit\Slink\User\Domain;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Encryption\EncryptionRegistry;
use Slink\Shared\Infrastructure\Encryption\EncryptionService;
use Slink\User\Domain\Enum\ApprovalPolicy;
use Slink\User\Domain\Enum\RegistrationPolicy;
use Slink\User\Domain\OAuthProvider;
use Slink\User\Domain\ValueObject\OAuth\ClientId;
use Slink\User\Domain\ValueObject\OAuth\ClientSecret;
use Slink\User\Domain\ValueObject\OAuth\DiscoveryUrl;
use Slink\User\Domain\ValueObject\OAuth\OAuthScopes;
use Slink\User\Domain\ValueObject\OAuth\OAuthType;
use Slink\User\Domain\ValueObject\OAuth\ProviderName;
use Slink\User\Domain\ValueObject\OAuth\ProviderSlug;

final class OAuthProviderSnapshotTest extends TestCase {
  private const string ENCRYPTION_SECRET = 'test-encryption-secret';

  protected function setUp(): void {
    EncryptionRegistry::setService(new EncryptionService(self::ENCRYPTION_SECRET));
  }

  #[Test]
  public function itDefaultsPoliciesToInheritWhenSnapshotStateOmitsThem(): void {
    $provider = OAuthProvider::create(
      ID::fromString('provider-id-1'),
      ProviderName::fromString('Google'),
      ProviderSlug::fromString('google'),
      OAuthType::fromString('oidc'),
      ClientId::fromString('client-id-123'),
      ClientSecret::fromString('client-secret-456'),
      DiscoveryUrl::fromString('https://accounts.google.com/.well-known/openid-configuration'),
      OAuthScopes::fromString('openid profile email'),
      RegistrationPolicy::Blocked,
      ApprovalPolicy::Required,
      true,
      1.0,
    );

    $reflection = new \ReflectionClass(OAuthProvider::class);

    /** @var array<string, mixed> $state */
    $state = $reflection->getMethod('createSnapshotState')->invoke($provider);
    unset($state['registrationPolicy'], $state['approvalPolicy']);

    $restored = $reflection->getMethod('reconstituteFromSnapshotState')
      ->invoke(null, ID::fromString('provider-id-1'), $state);

    $this->assertSame(
      RegistrationPolicy::Inherit,
      $reflection->getProperty('registrationPolicy')->getValue($restored),
    );
    $this->assertSame(
      ApprovalPolicy::Inherit,
      $reflection->getProperty('approvalPolicy')->getValue($restored),
    );
  }
}
