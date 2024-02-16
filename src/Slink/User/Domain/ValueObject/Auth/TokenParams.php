<?php

declare(strict_types=1);

namespace Slink\User\Domain\ValueObject\Auth;

use Slink\Shared\Domain\ValueObject\AbstractValueObject;

final readonly class TokenParams extends AbstractValueObject {
  /**
   * @param array<string, mixed> $payload
   * @param int $ttl
   */
  private function __construct(
    private array $payload,
    private int $ttl
  ) {}
  
  /**
   * @param array<string, mixed> $payload
   * @param int $ttl
   * @return self
   */
  public static function create(array $payload = [], int $ttl = 2592000): self {
    return new self($payload, $ttl);
  }
  
  /**
   * @return array<string, mixed>
   */
  public function getPayload(): array {
    return $this->payload;
  }
  
  /**
   * @return int
   */
  public function getTtl(): int {
    return $this->ttl;
  }
}