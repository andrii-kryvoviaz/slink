<?php

declare(strict_types=1);

namespace Slink\Collection\Domain\ValueObject;

use Slink\Shared\Domain\Exception\InvalidArgumentException;
use Slink\Shared\Domain\ValueObject\AbstractValueObject;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class CollectionDescription extends AbstractValueObject {
  private const int MAX_LENGTH = 500;

  private function __construct(
    #[Assert\Length(max: self::MAX_LENGTH)]
    private string $value,
  ) {
  }

  public static function fromString(string $description): self {
    $trimmed = trim($description);

    if (mb_strlen($trimmed) > self::MAX_LENGTH) {
      throw new InvalidArgumentException(sprintf('Collection description cannot exceed %d characters', self::MAX_LENGTH), 'description');
    }

    return new self($trimmed);
  }

  public static function empty(): self {
    return new self('');
  }

  public function getValue(): string {
    return $this->value;
  }

  public function isEmpty(): bool {
    return $this->value === '';
  }

  public function toString(): string {
    return $this->value;
  }
}
