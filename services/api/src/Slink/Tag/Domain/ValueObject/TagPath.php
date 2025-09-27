<?php

declare(strict_types=1);

namespace Slink\Tag\Domain\ValueObject;

use Slink\Shared\Domain\ValueObject\AbstractValueObject;

final readonly class TagPath extends AbstractValueObject {
  private const string SEPARATOR = '/';
  private const string PREFIX = '#';

  private function __construct(
    private string $value,
  ) {
  }

  public static function fromString(string $value): self {
    return new self($value);
  }

  public static function createRoot(TagName $name): self {
    return new self(self::PREFIX . $name->getValue());
  }

  public static function createChild(TagPath $parentPath, TagName $childName): self {
    return new self($parentPath->getValue() . self::SEPARATOR . $childName->getValue());
  }

  /**
   * @param array<string, string> $payload
   */
  public static function fromPayload(array $payload): self {
    return self::fromString($payload['value'] ?? $payload['path']);
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

  public function getDepth(): int {
    if (empty($this->value) || $this->value === self::PREFIX) {
      return 1;
    }

    return substr_count($this->value, self::SEPARATOR) + 1;
  }

  public function getParentPath(): ?self {
    if ($this->value === self::PREFIX) {
      return null;
    }
    
    $lastSeparatorPos = strrpos($this->value, self::SEPARATOR);

    if ($lastSeparatorPos === false) {
      return null;
    }

    $parentPathValue = substr($this->value, 0, $lastSeparatorPos);
    return $parentPathValue === self::PREFIX ? null : new self($parentPathValue);
  }

  public function getTagName(): string {
    $lastSeparatorPos = strrpos($this->value, self::SEPARATOR);

    if ($lastSeparatorPos === false) {
      return str_starts_with($this->value, self::PREFIX) 
        ? substr($this->value, 1) 
        : $this->value;
    }

    return substr($this->value, $lastSeparatorPos + 1);
  }

  public function isChild(): bool {
    return str_contains($this->value, self::SEPARATOR);
  }

  public function isParentOf(TagPath $other): bool {
    return str_starts_with($other->getValue(), $this->value . self::SEPARATOR);
  }

  public function isDescendantOf(TagPath $other): bool {
    return str_starts_with($this->value, $other->getValue() . self::SEPARATOR);
  }

  /**
   * @return array<TagPath>
   */
  public function getAncestorPaths(): array {
    $paths = [];
    $parts = explode(self::SEPARATOR, $this->value);

    for ($i = 1; $i < count($parts); $i++) {
      $ancestorPath = implode(self::SEPARATOR, array_slice($parts, 0, $i + 1));
      if ($ancestorPath !== self::PREFIX) {
        $paths[] = new self($ancestorPath);
      }
    }

    return $paths;
  }

  public function getDescendantSearchPattern(): string {
    return $this->value . self::SEPARATOR . '%';
  }

  public function isRoot(): bool {
    return !str_contains($this->value, self::SEPARATOR);
  }
}