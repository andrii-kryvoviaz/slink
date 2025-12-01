<?php

declare(strict_types=1);

namespace Slink\Comment\Domain\ValueObject;

use Slink\Shared\Domain\ValueObject\AbstractValueObject;

final readonly class CommentContent extends AbstractValueObject {
  private const int MAX_LENGTH = 2000;
  private const int MIN_LENGTH = 1;

  private function __construct(
    private string $value
  ) {
  }

  public static function fromString(string $content): self {
    $escaped = htmlspecialchars($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    self::validate($escaped);
    return new self($escaped);
  }

  private static function validate(string $content): void {
    $length = mb_strlen($content);

    if ($length < self::MIN_LENGTH) {
      throw new \InvalidArgumentException('Comment content cannot be empty');
    }

    if ($length > self::MAX_LENGTH) {
      throw new \InvalidArgumentException(
        sprintf('Comment content cannot exceed %d characters', self::MAX_LENGTH)
      );
    }
  }

  public function toString(): string {
    return $this->value;
  }

  public function equals(?AbstractValueObject $other): bool {
    return $other instanceof self && $this->value === $other->value;
  }
}
