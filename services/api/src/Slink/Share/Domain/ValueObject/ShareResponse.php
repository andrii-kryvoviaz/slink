<?php

declare(strict_types=1);

namespace Slink\Share\Domain\ValueObject;

use Slink\Share\Domain\Enum\ShareableType;
use Slink\Share\Domain\Share;
use Slink\Shared\Domain\ValueObject\AbstractValueObject;

final readonly class ShareResponse extends AbstractValueObject {
  private function __construct(
    private string $shareId,
    private string $shareUrl,
    private ShareableType $type,
    private bool $created,
  ) {}

  public static function fromShare(Share $share, string $shareUrl, bool $created): self {
    return new self(
      $share->aggregateRootId()->toString(),
      $shareUrl,
      $share->getShareable()->getShareableType(),
      $created,
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

  public function toPayload(): array {
    return [
      'shareId' => $this->shareId,
      'shareUrl' => $this->shareUrl,
      'type' => $this->type->value,
      'created' => $this->created,
    ];
  }
}
