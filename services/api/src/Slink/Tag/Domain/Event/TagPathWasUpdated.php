<?php

declare(strict_types=1);

namespace Slink\Tag\Domain\Event;

use EventSauce\EventSourcing\Serialization\SerializablePayload;
use Slink\Shared\Domain\Exception\Date\DateTimeException;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Tag\Domain\ValueObject\TagPath;

final readonly class TagPathWasUpdated implements SerializablePayload {
  public function __construct(
    public ID       $id,
    public TagPath  $path,
    public DateTime $updatedAt,
  ) {}

  /**
   * @return array<string, string|null>
   */
  public function toPayload(): array {
    return [
      'uuid' => $this->id->toString(),
      'path' => $this->path->getValue(),
      'updated_at' => $this->updatedAt->toString(),
    ];
  }

  /**
   * @param array<string, string|null> $payload
   * @throws DateTimeException
   */
  public static function fromPayload(array $payload): static {
    return new self(
      ID::fromString($payload['uuid'] ?? ''),
      TagPath::fromString($payload['path'] ?? ''),
      !empty($payload['updated_at']) ? DateTime::fromString($payload['updated_at']) : DateTime::now(),
    );
  }
}
