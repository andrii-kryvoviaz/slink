<?php

declare(strict_types=1);

namespace Slink\Comment\Domain\Event;

use EventSauce\EventSourcing\Serialization\SerializablePayload;
use Slink\Comment\Domain\ValueObject\CommentContent;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ID;

final readonly class CommentWasEdited implements SerializablePayload {
  public function __construct(
    public ID $id,
    public CommentContent $content,
    public DateTime $updatedAt,
  ) {
  }

  /**
   * @return array<string, mixed>
   */
  public function toPayload(): array {
    return [
      'id' => $this->id->toString(),
      'content' => $this->content->toString(),
      'updatedAt' => $this->updatedAt->toString(),
    ];
  }

  /**
   * @param array<string, mixed> $payload
   */
  public static function fromPayload(array $payload): static {
    return new self(
      ID::fromString($payload['id']),
      CommentContent::fromString($payload['content']),
      DateTime::fromString($payload['updatedAt']),
    );
  }
}
