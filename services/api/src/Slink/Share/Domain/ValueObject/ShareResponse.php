<?php

declare(strict_types=1);

namespace Slink\Share\Domain\ValueObject;

use Slink\Share\Domain\Enum\ShareableType;
use Slink\Share\Domain\Share;
use Slink\Share\Infrastructure\ReadModel\View\ShareView;
use Slink\Shared\Domain\ValueObject\AbstractValueObject;

final readonly class ShareResponse extends AbstractValueObject {
  private function __construct(
    private string $shareId,
    private string $shareUrl,
    private ShareableType $type,
    private bool $created,
    private ?string $expiresAt = null,
  ) {}

  public static function fromShare(Share|ShareView $share, string $shareUrl, bool $created = false): self {
    if ($share instanceof Share) {
      $shareId = $share->aggregateRootId()->toString();
    } else {
      $shareId = $share->getId();
    }

    return new self(
      $shareId,
      $shareUrl,
      $share->getShareable()->getShareableType(),
      $created,
      $share->getExpiresAt()?->toString(),
    );
  }

  public function getShareId(): string {
    return $this->shareId;
  }

  public function getShareUrl(): string {
    return $this->shareUrl;
  }

  public function getType(): ShareableType {
    return $this->type;
  }

  public function wasCreated(): bool {
    return $this->created;
  }

  public function getExpiresAt(): ?string {
    return $this->expiresAt;
  }

  /**
   * @return array<string, mixed>
   */
  public function toPayload(): array {
    return [
      'shareId' => $this->shareId,
      'shareUrl' => $this->shareUrl,
      'type' => $this->type->value,
      'created' => $this->created,
      'expiresAt' => $this->expiresAt,
    ];
  }
}
