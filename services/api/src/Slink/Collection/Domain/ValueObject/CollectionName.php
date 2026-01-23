<?php

declare(strict_types=1);

namespace Slink\Collection\Domain\ValueObject;

use Slink\Shared\Domain\Exception\InvalidArgumentException;
use Slink\Shared\Domain\ValueObject\AbstractValueObject;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class CollectionName extends AbstractValueObject {
  private const int MAX_LENGTH = 100;

  private function __construct(
    #[Assert\NotBlank]
    #[Assert\Length(max: self::MAX_LENGTH)]
    private string $value,
  ) {
  }

  public static function fromString(string $name): self {
    $trimmed = trim($name);

    if (empty($trimmed)) {
      throw new InvalidArgumentException('Collection name cannot be empty', 'name');
    }

    if (mb_strlen($trimmed) > self::MAX_LENGTH) {
      throw new InvalidArgumentException(sprintf('Collection name cannot exceed %d characters', self::MAX_LENGTH), 'name');
    }

    return new self($trimmed);
  }

  public function getValue(): string {
    return $this->value;
  }

  public function toString(): string {
    return $this->value;
  }
}
