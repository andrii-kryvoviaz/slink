<?php

declare(strict_types=1);

namespace Slink\Shared\Domain\ValueObject;

final readonly class ResourceData {
  /**
   * @param array<string, array<string, mixed>> $data
   */
  public function __construct(
    private array $data = [],
  ) {
  }

  public function get(string $key, string $id, mixed $default = null): mixed {
    return $this->data[$key][$id] ?? $default;
  }

  public function has(string $key, string $id): bool {
    return isset($this->data[$key][$id]);
  }

  /**
   * @param array<string, mixed> $providerData
   */
  public function withProvider(string $key, array $providerData): self {
    return new self([...$this->data, $key => $providerData]);
  }
}
