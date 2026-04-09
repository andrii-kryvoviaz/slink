<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class Username extends Constraint {
  /**
   * @param mixed $options
   * @param array<string>|null $groups
   * @param mixed $payload
   * @param int $minLength
   * @param int $maxLength
   * @param string $tooShortMessage
   * @param string $tooLongMessage
   * @param string $invalidCharactersMessage
   * @param string $consecutiveSpecialCharsMessage
   * @param string $reservedUsernameMessage
   * @param array<string> $reservedUsernames
   */
  public function __construct(
    mixed         $options = null,
    ?array        $groups = null,
    mixed         $payload = null,
    public int    $minLength = 3,
    public int    $maxLength = 30,
    public string $tooShortMessage = 'AUTH_USERNAME_TOO_SHORT',
    public string $tooLongMessage = 'AUTH_USERNAME_TOO_LONG',
    public string $invalidCharactersMessage = 'AUTH_USERNAME_INVALID_CHARACTERS',
    public string $consecutiveSpecialCharsMessage = 'AUTH_USERNAME_CONSECUTIVE_SPECIAL',
    public string $reservedUsernameMessage = 'AUTH_USERNAME_RESERVED',
    public array  $reservedUsernames = ['anonymous'],
  ) {
    parent::__construct($options, $groups, $payload);
  }
}
