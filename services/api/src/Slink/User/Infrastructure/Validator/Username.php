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
    public string $tooShortMessage = 'Username must be at least {{ limit }} characters long.',
    public string $tooLongMessage = 'Username must be at most {{ limit }} characters long.',
    public string $invalidCharactersMessage = 'Username can only contain lowercase letters, numbers, underscores, hyphens, and periods.',
    public string $consecutiveSpecialCharsMessage = 'Username cannot contain consecutive special characters.',
    public string $reservedUsernameMessage = '`{{ value }}` is a reserved username.',
    public array  $reservedUsernames = ['anonymous'],
  ) {
    parent::__construct($options, $groups, $payload);
  }
}
