<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\Validator;

use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class FutureDateValidator extends ConstraintValidator {
  public function validate(mixed $value, Constraint $constraint): void {
    if (!$constraint instanceof FutureDate) {
      throw new UnexpectedTypeException($constraint, FutureDate::class);
    }

    if (null === $value || $value === '') {
      return;
    }

    if (!is_string($value)) {
      throw new UnexpectedValueException($value, 'string');
    }

    try {
      if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}$/', $value)) {
        $value = $value . '.000000+00:00';
      }
      
      $dateTime = DateTime::fromString($value);
      $now = DateTime::now();

      if ($dateTime->isBeforeEquals($now)) {
        $this->context->buildViolation($constraint->message)
          ->addViolation();
      }
    } catch (\Exception) {
      $this->context->buildViolation('This value is not a valid datetime.')
        ->addViolation();
    }
  }
}
