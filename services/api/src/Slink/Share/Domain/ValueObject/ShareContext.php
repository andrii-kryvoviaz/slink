<?php

declare(strict_types=1);

namespace Slink\Share\Domain\ValueObject;

use Slink\Shared\Domain\ValueObject\AbstractValueObject;
use Slink\Shared\Domain\ValueObject\ID;

final readonly class ShareContext extends AbstractValueObject {
  public function __construct(
    private readonly ?string $shortCode = null,
    private readonly ?ID $shortUrlId = null,
  ) {
  }

  public static function empty(): self {
    return new self();
  }

  public static function withShortUrl(ID $shortUrlId, string $shortCode): self {
    return new self($shortCode, $shortUrlId);
  }

  /**
   * @phpstan-assert-if-true !null $this->shortCode
   * @phpstan-assert-if-true !null $this->shortUrlId
   * @phpstan-assert-if-true string $this->getShortCode()
   * @phpstan-assert-if-true ID $this->getShortUrlId()
   */
  public function hasShortUrl(): bool {
    return $this->shortCode !== null && $this->shortUrlId !== null;
  }

  public function getShortCode(): ?string {
    return $this->shortCode;
  }

  public function getShortUrlId(): ?ID {
    return $this->shortUrlId;
  }

  /**
   * @return array<string, mixed>
   */
  public function toPayload(): array {
    return [
      'shortCode' => $this->shortCode,
      'shortUrlId' => $this->shortUrlId?->toString(),
    ];
  }

  /**
   * @param array<string, mixed> $payload
   */
  public static function fromPayload(array $payload): self {
    return new self(
      $payload['shortCode'] ?? null,
      isset($payload['shortUrlId']) ? ID::fromString($payload['shortUrlId']) : null,
    );
  }
}
