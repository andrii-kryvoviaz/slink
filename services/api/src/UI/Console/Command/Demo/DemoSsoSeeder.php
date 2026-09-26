<?php

declare(strict_types=1);

namespace UI\Console\Command\Demo;

use Slink\Shared\Application\Command\CommandTrait;
use Slink\User\Application\Command\CreateOAuthProvider\CreateOAuthProviderCommand;
use Slink\User\Domain\Repository\OAuthProviderRepositoryInterface;
use Slink\User\Domain\ValueObject\OAuth\ProviderSlug;

final class DemoSsoSeeder {
  use CommandTrait;

  private const array PROVIDERS = [
    'authentik' => ['Authentik', 'https://auth.example.com/application/o/slink/.well-known/openid-configuration'],
    'keycloak' => ['Keycloak', 'https://sso.example.com/realms/slink/.well-known/openid-configuration'],
  ];

  public function __construct(
    private readonly OAuthProviderRepositoryInterface $providerRepository,
  ) {
  }

  public function seed(DemoSeedReport $report): void {
    foreach (self::PROVIDERS as $slug => [$name, $discoveryUrl]) {
      $report->attempt($name, fn() => $this->ensureProvider($slug, $name, $discoveryUrl, $report));
    }
  }

  private function ensureProvider(string $slug, string $name, string $discoveryUrl, DemoSeedReport $report): void {
    if ($this->providerRepository->findByProvider(ProviderSlug::fromString($slug)) !== null) {
      $report->skipped($name);

      return;
    }

    $this->handle(new CreateOAuthProviderCommand(
      name: $name,
      slug: $slug,
      clientId: 'slink-demo',
      discoveryUrl: $discoveryUrl,
      clientSecret: 'slink-demo-secret',
      enabled: false,
    ));
    $report->created($name);
  }
}
