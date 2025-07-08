<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class FutureDate extends Constraint {
  public string $message = 'The expiry date must be in the future.';

  public function __construct(
    mixed $options = null,
    ?array $groups = null,
    mixed $payload = null,
    string $message = 'The expiry date must be in the future.'
  ) {
    $this->message = $message;
    parent::__construct($options ?? [], $groups, $payload);
  }
}
