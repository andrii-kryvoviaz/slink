<?php

declare(strict_types=1);

namespace Slink\Settings\Domain\Specification;

use Slink\Settings\Domain\ValueObject\AbstractSettingsValueObject;

interface SignInMethodSpecificationInterface {
  /**
   * @param AbstractSettingsValueObject $settings
   * @return bool
   */
  public function isSatisfiedBy(AbstractSettingsValueObject $settings): bool;
}
