<?php

declare(strict_types=1);

namespace Slink\Shared\Domain\ValueObject;

final readonly class EscapedString {
  private const int FLAGS = ENT_QUOTES | ENT_HTML5;
  private const string ENCODING = 'UTF-8';

  private function __construct(private string $value) {
  }

  public static function fromString(string $value): self {
    return new self(htmlspecialchars(self::decode($value), self::FLAGS, self::ENCODING));
  }

  public static function fromTrusted(string $value): self {
    return new self($value);
  }

  public static function empty(): self {
    return new self('');
  }

  public function getValue(): string {
    return $this->value;
  }

  public function toString(): string {
    return $this->value;
  }

  public function isEmpty(): bool {
    return $this->value === '';
  }

  public function unescaped(): string {
    return self::decode($this->value);
  }

  public function length(): int {
    return mb_strlen($this->unescaped());
  }

  private static function decode(string $value): string {
    return html_entity_decode($value, self::FLAGS, self::ENCODING);
  }
}
