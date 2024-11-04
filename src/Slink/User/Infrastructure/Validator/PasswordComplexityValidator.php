<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\Validator;

use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class PasswordComplexityValidator extends ConstraintValidator {
  public function __construct(private readonly ConfigurationProviderInterface $configurationProvider) {
  }
  
  /**
   * @param mixed $value
   * @param PasswordComplexity $constraint
   * @return void
   */
  public function validate(#[\SensitiveParameter] mixed $value, Constraint $constraint): void {
    if (!$constraint instanceof PasswordComplexity) {
      throw new UnexpectedTypeException($constraint, PasswordComplexity::class);
    }
    
    if (null === $value) {
      $value = '';
    }
    
    if (!\is_string($value)) {
      throw new UnexpectedValueException($value, 'string');
    }
    
    if($this->configurationProvider->has('user.password.minLength')){
      $constraint->minLength = $this->configurationProvider->get('user.password.minLength') > $constraint->minLength
        ? $this->configurationProvider->get('user.password.minLength')
        : $constraint->minLength;
    }

    if($this->configurationProvider->has('user.password.requirements')) {
      $constraint->requirements = $this->configurationProvider->get('user.password.requirements');
    }
    
    if ($constraint->minLength > 0 && (mb_strlen($value) < $constraint->minLength)) {
      $this->context->buildViolation($constraint->tooShortMessage)
        ->setParameter('{{ limit }}', (string) $constraint->minLength)
        ->addViolation();
    }
    
    if($constraint->requirements === 0) {
      return;
    }
    
    if($constraint->requirements & 1 && !preg_match('/\p{N}/u', $value)) {
      $this->context->buildViolation($constraint->missingNumbersMessage)
        ->addViolation();
    }
    
    if($constraint->requirements & 2 && !preg_match('/\p{Ll}/u', $value)) {
      $this->context->buildViolation($constraint->missingLowercaseMessage)
        ->addViolation();
    }
    
    if($constraint->requirements & 4 && !preg_match('/\p{Lu}/u', $value)) {
      $this->context->buildViolation($constraint->missingUppercaseMessage)
        ->addViolation();
    }
    
    if($constraint->requirements & 8 && !preg_match('/\p{P}/u', $value)) {
      $this->context->buildViolation($constraint->missingSpecialCharactersMessage)
        ->addViolation();
    }
  }
}