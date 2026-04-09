<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class PasswordComplexity extends Constraint {
  /**
   * @param mixed|null $options
   * @param array<int, string>|null $groups
   * @param mixed|null $payload
   * @param int $minLength
   * @param int $requirements
   * @param string $tooShortMessage
   * @param string $missingNumbersMessage
   * @param string $missingLowercaseMessage
   * @param string $missingUppercaseMessage
   * @param string $missingSpecialCharactersMessage
   *
   *  Requirements (bitmask):
   *  1 - contains numbers
   *  2 - contains lowercase letters
   *  4 - contains uppercase letters
   *  8 - contains special characters
   */
  public function __construct(
    mixed $options = null,
    ?array $groups = null,
    mixed $payload = null,
    public int $minLength = 6,
    public int $requirements = 15,
    public string $tooShortMessage = 'AUTH_PASSWORD_TOO_SHORT',
    public string $missingNumbersMessage = 'AUTH_PASSWORD_MISSING_NUMBER',
    public string $missingLowercaseMessage = 'AUTH_PASSWORD_MISSING_LOWERCASE',
    public string $missingUppercaseMessage = 'AUTH_PASSWORD_MISSING_UPPERCASE',
    public string $missingSpecialCharactersMessage = 'AUTH_PASSWORD_MISSING_SPECIAL',
  ) {
    parent::__construct($options ?? [], $groups, $payload);
  }
}
