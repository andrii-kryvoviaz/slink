<?php

declare(strict_types=1);

namespace Slink\Tag\Domain\ValueObject;

use InvalidArgumentException;
use Slink\Shared\Domain\ValueObject\AbstractValueObject;

final readonly class TagName extends AbstractValueObject {
  private const int MIN_LENGTH = 1;
  private const int MAX_LENGTH = 50;

  private function __construct(
    private string $value,
  ) {
    $this->validate();
  }

  public static function fromString(string $value): self {
    return new self(trim($value));
  }

  /**
   * @param array<string, string> $payload
   */
  public static function fromPayload(array $payload): self {
    return self::fromString($payload['value'] ?? $payload['name']);
  }

  public function getValue(): string {
    return $this->value;
  }

  /**
   * @return array<string, string>
   */
  public function toPayload(): array {
    return ['value' => $this->value];
  }

  private function validate(): void {
    if (empty($this->value)) {
      throw new InvalidArgumentException('Tag name cannot be empty');
    }

    if (mb_strlen($this->value) > self::MAX_LENGTH) {
      throw new InvalidArgumentException(
        sprintf('Tag name must be between %d and %d characters', self::MIN_LENGTH, self::MAX_LENGTH)
      );
    }

    if (str_contains($this->value, '/')) {
      throw new InvalidArgumentException('Tag name cannot contain a slash');
    }
  }
}