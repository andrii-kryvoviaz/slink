<?php

declare(strict_types=1);

namespace Slink\Share\Domain\ValueObject;

use Slink\Share\Domain\Enum\ShareType;
use Slink\Shared\Domain\ValueObject\AbstractValueObject;

final readonly class ShareResult extends AbstractValueObject {
  private function __construct(
    private ShareType $type,
    private ?string $targetUrl = null,
    private ?string $shortCode = null,
  ) {}

  public static function shortUrl(?string $shortCode): self {
    return new self(ShareType::ShortUrl, shortCode: $shortCode);
  }

  public static function signed(string $targetUrl): self {
    return new self(ShareType::Signed, targetUrl: $targetUrl);
  }

  public function getType(): ShareType {
    return $this->type;
  }

  public function getShortCode(): ?string {
    return $this->shortCode;
  }

  public function getTargetUrl(): ?string {
    return $this->targetUrl;
  }

  public function getUrl(): string {
    return match ($this->type) {
      ShareType::ShortUrl => "i/{$this->shortCode}",
      ShareType::Signed => $this->targetUrl ?? '',
    };
  }

  /**
   * @return array<string, mixed>
   */
  public function toPayload(): array {
    return match ($this->type) {
      ShareType::ShortUrl => [
        'type' => $this->type->value,
        'shortCode' => $this->shortCode,
      ],
      ShareType::Signed => [
        'type' => $this->type->value,
        'targetUrl' => $this->targetUrl,
      ],
    };
  }
}
