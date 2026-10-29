<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\Validator;

use Slink\User\Domain\Exception\InvalidUsernameException;
use Slink\User\Domain\Specification\SignUpUsernameSpecificationInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class UsernameValidator extends ConstraintValidator {
  public function __construct(
    private readonly SignUpUsernameSpecificationInterface $signUpUsernameSpecification,
  ) {
  }

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

    try {
      $this->signUpUsernameSpecification->validate($value);
    } catch (InvalidUsernameException $exception) {
      $this->context->buildViolation($exception->getMessage())->addViolation();
    }
  }
}
