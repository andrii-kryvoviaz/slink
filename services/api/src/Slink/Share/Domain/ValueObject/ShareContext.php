<?php

declare(strict_types=1);

namespace Slink\Share\Domain\ValueObject;

use Slink\Shared\Domain\ValueObject\AbstractValueObject;
use Slink\Shared\Domain\ValueObject\ID;

final readonly class ShareContext extends AbstractValueObject {
  public function __construct(
    private ShareableReference $shareable,
    private ?string $shortCode = null,
    private ?ID $shortUrlId = null,
  ) {
  }

  public static function for(ShareableReference $shareable): self {
    return new self($shareable);
  }

  public function withShortUrl(ID $shortUrlId, string $shortCode): self {
    return new self($this->shareable, $shortCode, $shortUrlId);
  }

  public function getShareable(): ShareableReference {
    return $this->shareable;
  }

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
  public static function fromPayload(array $payload, ?ShareableReference $shareable = null): self {
    return new self(
      $shareable ?? ShareableReference::fromPayload($payload['shareable'] ?? []),
      $payload['shortCode'] ?? null,
      isset($payload['shortUrlId']) ? ID::fromString($payload['shortUrlId']) : null,
    );
  }
}
