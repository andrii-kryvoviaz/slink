<?php

declare(strict_types=1);

namespace Slink\Comment\Domain\Event;

use EventSauce\EventSourcing\Serialization\SerializablePayload;
use Slink\Comment\Domain\ValueObject\CommentContent;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ID;

final readonly class CommentWasCreated implements SerializablePayload {
  public function __construct(
    public ID $id,
    public ID $imageId,
    public ID $userId,
    public ?ID $referencedCommentId,
    public CommentContent $content,
    public DateTime $createdAt,
  ) {
  }

  /**
   * @return array<string, mixed>
   */
  public function toPayload(): array {
    return [
      'uuid' => $this->id->toString(),
      'image' => $this->imageId->toString(),
      'user' => $this->userId->toString(),
      'content' => $this->content->toString(),
      'createdAt' => $this->createdAt->toString(),
      'referencedCommentId' => $this->referencedCommentId?->toString(),
    ];
  }

  /**
   * @param array<string, mixed> $payload
   */
  public static function fromPayload(array $payload): static {
    return new self(
      ID::fromString($payload['uuid']),
      ID::fromString($payload['image']),
      ID::fromString($payload['user']),
      isset($payload['referencedCommentId']) ? ID::fromString($payload['referencedCommentId']) : null,
      CommentContent::fromString($payload['content']),
      DateTime::fromString($payload['createdAt']),
    );
  }
}
