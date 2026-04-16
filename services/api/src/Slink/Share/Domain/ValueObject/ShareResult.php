<?php

declare(strict_types=1);

namespace Slink\Share\Domain\ValueObject;

use Slink\Share\Domain\Enum\ShareType;
use Slink\Shared\Domain\ValueObject\AbstractValueObject;

final readonly class ShareResult extends AbstractValueObject {
  private function __construct(
    private ShareType $type,
    private ?TargetPath $targetPath = null,
    private ?string $shortCode = null,
  ) {}

  public static function shortUrl(?string $shortCode): self {
    return new self(ShareType::ShortUrl, shortCode: $shortCode);
  }

  public static function signed(TargetPath $targetPath): self {
    return new self(ShareType::Signed, targetPath: $targetPath);
  }

  public function getType(): ShareType {
    return $this->type;
  }

  public function getShortCode(): ?string {
    return $this->shortCode;
  }

  public function getTargetPath(): ?TargetPath {
    return $this->targetPath;
  }

  public function getUrl(): string {
    return match ($this->type) {
      ShareType::ShortUrl => "i/{$this->shortCode}",
      ShareType::Signed => $this->targetPath?->toString() ?? '',
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
        'targetUrl' => $this->targetPath?->toString(),
      ],
    };
  }
}
