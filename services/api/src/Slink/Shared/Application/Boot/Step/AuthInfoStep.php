<?php

declare(strict_types=1);

namespace Slink\Shared\Application\Boot\Step;

use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Application\Boot\BootCategory;
use Slink\Shared\Application\Boot\BootContext;
use Slink\Shared\Application\Boot\BootResult;
use Slink\Shared\Application\Boot\BootStatus;
use Slink\Shared\Application\Boot\BootStepInterface;
use Slink\User\Domain\Filter\OAuthProviderFilter;
use Slink\User\Domain\Repository\OAuthProviderRepositoryInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: 70)]
final readonly class AuthInfoStep implements BootStepInterface {
  public function __construct(
    private ConfigurationProviderInterface $config,
    private OAuthProviderRepositoryInterface $providers,
  ) {}

  public function label(): string {
    return 'access';
  }

  public function category(): BootCategory {
    return BootCategory::Config;
  }

  public function run(BootContext $context): BootResult {
    $approval = (bool) $this->config->get('user.approvalRequired');
    $minLength = (int) $this->config->get('user.password.minLength');

    $settings = [
      ['Public User Registration', $this->boolLabel('user.allowRegistration')],
      ['New Account Approval', $approval ? 'Required' : 'Not Required'],
      ['Unauthenticated Access', $this->boolLabel('access.allowUnauthenticatedAccess')],
      ['Anonymous Guest Uploads', $this->boolLabel('access.allowGuestUploads')],
      ['Auth Required for Media Shares', $this->boolLabel('access.requireAuthForMediaShares')],
      ['Auth Required for Collection Shares', $this->boolLabel('access.requireAuthForCollectionShares')],
      ['Force HTTPS Connections', $this->boolLabel('access.requireSsl')],
      ['Minimum Password Length', sprintf('%d characters', $minLength)],
    ];

    if (!$this->isLockedOut()) {
      return BootResult::settings($settings);
    }

    return new BootResult(
      BootStatus::Warn,
      'Local authentication is disabled and no SSO provider is enabled, so nobody can sign in',
      settings: $settings,
    );
  }

  private function isLockedOut(): bool {
    if ((bool) $this->config->get('user.localAuthEnabled')) {
      return false;
    }

    return $this->providers->getProviders(new OAuthProviderFilter(enabledOnly: true)) === [];
  }

  private function boolLabel(string $key): string {
    return ((bool) $this->config->get($key)) ? 'Enabled' : 'Disabled';
  }
}
