<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class UsernameValidator extends ConstraintValidator {
  public function validate(mixed $value, Constraint $constraint): void {
    if (!$constraint instanceof Username) {
      throw new UnexpectedTypeException($constraint, Username::class);
    }

    if (null === $value || '' === $value) {
      return;
    }

    if (!\is_string($value)) {
      throw new UnexpectedValueException($value, 'string');
    }

    $length = mb_strlen($value);

    if ($length < $constraint->minLength) {
      $this->context->buildViolation($constraint->tooShortMessage)
        ->setParameter('{{ limit }}', (string)$constraint->minLength)
        ->addViolation();
      return;
    }

    if ($length > $constraint->maxLength) {
      $this->context->buildViolation($constraint->tooLongMessage)
        ->setParameter('{{ limit }}', (string)$constraint->maxLength)
        ->addViolation();
      return;
    }

    if (!preg_match('/^[a-z0-9_\-\.]+$/', $value)) {
      $this->context->buildViolation($constraint->invalidCharactersMessage)
        ->addViolation();
      return;
    }

    if (preg_match('/(_|-|\.){2}/', $value)) {
      $this->context->buildViolation($constraint->consecutiveSpecialCharsMessage)
        ->addViolation();
      return;
    }

    foreach ($constraint->reservedUsernames as $reserved) {
      if (strtolower($value) === strtolower($reserved)) {
        $this->context->buildViolation($constraint->reservedUsernameMessage)
          ->setParameter('{{ value }}', $value)
          ->addViolation();
        return;
      }
    }
  }
}
