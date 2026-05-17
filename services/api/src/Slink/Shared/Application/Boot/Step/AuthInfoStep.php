<?php

declare(strict_types=1);

namespace Slink\Shared\Application\Boot\Step;

use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Application\Boot\BootCategory;
use Slink\Shared\Application\Boot\BootContext;
use Slink\Shared\Application\Boot\BootResult;
use Slink\Shared\Application\Boot\BootStepInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: 70)]
final readonly class AuthInfoStep implements BootStepInterface {
  public function __construct(
    private ConfigurationProviderInterface $config,
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

    return BootResult::settings([
      ['Public User Registration', $this->boolLabel('user.allowRegistration')],
      ['New Account Approval', $approval ? 'Required' : 'Not Required'],
      ['Unauthenticated Access', $this->boolLabel('access.allowUnauthenticatedAccess')],
      ['Anonymous Guest Uploads', $this->boolLabel('access.allowGuestUploads')],
      ['Auth Required for Media Shares', $this->boolLabel('access.requireAuthForMediaShares')],
      ['Auth Required for Collection Shares', $this->boolLabel('access.requireAuthForCollectionShares')],
      ['Force HTTPS Connections', $this->boolLabel('access.requireSsl')],
      ['Minimum Password Length', sprintf('%d characters', $minLength)],
    ]);
  }

  private function boolLabel(string $key): string {
    return ((bool) $this->config->get($key)) ? 'Enabled' : 'Disabled';
  }
}
