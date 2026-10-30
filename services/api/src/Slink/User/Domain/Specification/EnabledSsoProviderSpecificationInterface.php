<?php

declare(strict_types=1);

namespace Slink\User\Domain\Specification;

interface EnabledSsoProviderSpecificationInterface {
  public function isSatisfied(): bool;
}
