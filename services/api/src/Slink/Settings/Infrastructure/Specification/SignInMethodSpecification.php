<?php

declare(strict_types=1);

namespace Slink\Settings\Infrastructure\Specification;

use Slink\Settings\Domain\Specification\SignInMethodSpecificationInterface;
use Slink\Settings\Domain\ValueObject\AbstractSettingsValueObject;
use Slink\Settings\Domain\ValueObject\User\UserSettings;
use Slink\User\Domain\Specification\EnabledSsoProviderSpecificationInterface;

final readonly class SignInMethodSpecification implements SignInMethodSpecificationInterface {
  public function __construct(
    private EnabledSsoProviderSpecificationInterface $enabledSsoProvider,
  ) {}

  public function isSatisfiedBy(AbstractSettingsValueObject $settings): bool {
    if (!$settings instanceof UserSettings) {
      return true;
    }

    return $settings->isLocalAuthEnabled() || $this->enabledSsoProvider->isSatisfied();
  }
}
