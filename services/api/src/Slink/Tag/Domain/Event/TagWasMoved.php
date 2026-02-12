<?php

declare(strict_types=1);

namespace Slink\Tag\Domain\Event;

use EventSauce\EventSourcing\Serialization\SerializablePayload;
use Slink\Shared\Domain\Exception\Date\DateTimeException;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Tag\Domain\ValueObject\TagPath;

final readonly class TagWasMoved implements SerializablePayload {
  public function __construct(
    public ID $id,
    public TagPath $oldPath,
    public TagPath $newPath,
    public ?ID $oldParentId = null,
    public ?ID $newParentId = null,
    public ?DateTime $updatedAt = null,
  ) {}

  /**
   * @return array<string, string|null>
   */
  public function toPayload(): array {
    return [
      'uuid' => $this->id->toString(),
      'old_path' => $this->oldPath->getValue(),
      'new_path' => $this->newPath->getValue(),
      'old_parent_id' => $this->oldParentId?->toString(),
      'new_parent_id' => $this->newParentId?->toString(),
      'updated_at' => ($this->updatedAt ?? DateTime::now())->toString(),
    ];
  }

  /**
   * @param array<string, string|null> $payload
   * @throws DateTimeException
   */
  public static function fromPayload(array $payload): static {
    return new self(
      ID::fromString($payload['uuid'] ?? ''),
      TagPath::fromString($payload['old_path'] ?? ''),
      TagPath::fromString($payload['new_path'] ?? ''),
      !empty($payload['old_parent_id']) ? ID::fromString($payload['old_parent_id']) : null,
      !empty($payload['new_parent_id']) ? ID::fromString($payload['new_parent_id']) : null,
      !empty($payload['updated_at']) ? DateTime::fromString($payload['updated_at']) : null,
    );
  }
}
