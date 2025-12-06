<?php

declare(strict_types=1);

namespace Slink\Share\Domain\Event;

use EventSauce\EventSourcing\Serialization\SerializablePayload;
use Slink\Share\Domain\ValueObject\ShareContext;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ID;

final readonly class ShareWasCreated implements SerializablePayload {
  public function __construct(
    public ID $id,
    public ID $imageId,
    public string $targetUrl,
    public DateTime $createdAt,
    public ShareContext $context,
  ) {
  }

  /**
   * @return array<string, mixed>
   */
  public function toPayload(): array {
    return [
      'uuid' => $this->id->toString(),
      'image' => $this->imageId->toString(),
      'targetUrl' => $this->targetUrl,
      'createdAt' => $this->createdAt->toString(),
      'context' => $this->context->toPayload(),
    ];
  }

  /**
   * @param array<string, mixed> $payload
   */
  public static function fromPayload(array $payload): static {
    return new self(
      ID::fromString($payload['uuid']),
      ID::fromString($payload['image']),
      $payload['targetUrl'],
      DateTime::fromString($payload['createdAt']),
      ShareContext::fromPayload($payload['context'] ?? []),
    );
  }
}
