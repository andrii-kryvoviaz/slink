<?php

declare(strict_types=1);

namespace Slink\User\Domain\Specification;

use Slink\User\Domain\Exception\InvalidUsernameException;
use Slink\User\Domain\ValueObject\Username;

final readonly class SignUpUsernameSpecification implements SignUpUsernameSpecificationInterface {
  private const string ALLOWED_CHARACTERS = '/\A[a-z0-9_.-]+\z/';
  private const string CONSECUTIVE_SPECIAL_CHARACTERS = '/[_.-]{2}/';
  private const array RESERVED_USERNAMES = ['anonymous'];

  public function isSatisfiedBy(string $username): bool {
    try {
      $this->validate($username);
    } catch (InvalidUsernameException) {
      return false;
    }

    return true;
  }

  public function validate(string $username): void {
    $length = mb_strlen($username);

    if ($length < Username::MIN_LENGTH) {
      throw new InvalidUsernameException(sprintf('Username must be at least %d characters long.', Username::MIN_LENGTH));
    }

    if ($length > Username::MAX_LENGTH) {
      throw new InvalidUsernameException(sprintf('Username must be at most %d characters long.', Username::MAX_LENGTH));
    }

    if (!preg_match(self::ALLOWED_CHARACTERS, $username)) {
      throw new InvalidUsernameException('Username can only contain lowercase letters, numbers, underscores, hyphens, and periods.');
    }

    if (preg_match(self::CONSECUTIVE_SPECIAL_CHARACTERS, $username)) {
      throw new InvalidUsernameException('Username cannot contain consecutive special characters.');
    }

    if (\in_array($username, self::RESERVED_USERNAMES, true)) {
      throw new InvalidUsernameException(sprintf('`%s` is a reserved username.', $username));
    }
  }
}
