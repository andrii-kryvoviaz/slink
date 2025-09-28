<?php

declare(strict_types=1);

namespace Slink\Tag\Domain\Event;

use EventSauce\EventSourcing\Serialization\SerializablePayload;
use Slink\Shared\Domain\Exception\Date\DateTimeException;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Tag\Domain\ValueObject\TagName;
use Slink\Tag\Domain\ValueObject\TagPath;

final readonly class TagWasCreated implements SerializablePayload {
  public function __construct(
    public ID $id,
    public ID $userId,
    public TagName $name,
    public TagPath $path,
    public ?ID $parentId = null,
    public ?DateTime $createdAt = null,
  ) {}

  /**
   * @return array<string, string|null>
   */
  public function toPayload(): array {
    return [
      'uuid' => $this->id->toString(),
      'user_id' => $this->userId->toString(),
      'name' => $this->name->getValue(),
      'path' => $this->path->getValue(),
      'parent_id' => $this->parentId?->toString(),
      'created_at' => ($this->createdAt ?? DateTime::now())->toString(),
    ];
  }

  /**
   * @param array<string, string|null> $payload
   * @throws DateTimeException
   */
  public static function fromPayload(array $payload): static {
    return new self(
      ID::fromString($payload['uuid'] ?? ''),
      ID::fromString($payload['user_id'] ?? ''),
      TagName::fromString($payload['name'] ?? ''),
      TagPath::fromString($payload['path'] ?? ''),
      !empty($payload['parent_id']) ? ID::fromString($payload['parent_id']) : null,
      !empty($payload['created_at']) ? DateTime::fromString($payload['created_at']) : null,
    );
  }
}