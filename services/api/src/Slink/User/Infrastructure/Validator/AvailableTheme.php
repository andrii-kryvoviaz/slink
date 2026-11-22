<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class AvailableTheme extends Constraint {
  public string $message = 'Invalid display theme.';
}
