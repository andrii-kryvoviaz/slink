<?php

declare(strict_types=1);

namespace Slink\Shared\Domain\ValueObject;

use Slink\Shared\Domain\Exception\InvalidValueObjectException;

abstract readonly class AbstractStringValueObject extends AbstractValueObject {
  protected function __construct(protected string $value) {
    static::validate($value);
  }

  protected static function label(): string {
    return (new \ReflectionClass(static::class))->getShortName();
  }

  protected static function validate(string $value): void {
    if ($value === '') {
      throw new InvalidValueObjectException(static::label(), 'cannot be empty');
    }
  }

  public function getValue(): string {
    return $this->value;
  }

  public function toString(): string {
    return $this->value;
  }
}
