<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class Username extends Constraint {
}
