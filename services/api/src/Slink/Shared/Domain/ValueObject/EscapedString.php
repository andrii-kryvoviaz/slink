<?php

declare(strict_types=1);

namespace Slink\Shared\Domain\ValueObject;

final readonly class EscapedString {
  private function __construct(private string $value) {
  }

  public static function fromString(string $value): self {
    $decoded = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $escaped = htmlspecialchars($decoded, ENT_QUOTES | ENT_HTML5, 'UTF-8');

    return new self($escaped);
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

  public function length(): int {
    return mb_strlen(html_entity_decode($this->value, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
  }
}
