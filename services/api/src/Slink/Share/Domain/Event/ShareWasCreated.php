<?php

declare(strict_types=1);

namespace Slink\Share\Domain\Event;

use EventSauce\EventSourcing\Serialization\SerializablePayload;
use Slink\Share\Domain\ValueObject\ShareableReference;
use Slink\Share\Domain\ValueObject\ShareContext;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ID;

final readonly class ShareWasCreated implements SerializablePayload {
  public function __construct(
    public ID $id,
    public ShareableReference $shareable,
    public string $targetUrl,
    public DateTime $createdAt,
    public ShareContext $context,
  ) {
  }

  public function toPayload(): array {
    return [
      'uuid' => $this->id->toString(),
      'shareable' => $this->shareable->toPayload(),
      'targetUrl' => $this->targetUrl,
      'createdAt' => $this->createdAt->toString(),
      'context' => $this->context->toPayload(),
    ];
  }

  public static function fromPayload(array $payload): static {
    if (isset($payload['image'])) {
      $shareable = ShareableReference::forImage(ID::fromString($payload['image']));
    } else {
      $shareable = ShareableReference::fromPayload($payload['shareable']);
    }

    return new self(
      ID::fromString($payload['uuid']),
      $shareable,
      $payload['targetUrl'],
      DateTime::fromString($payload['createdAt']),
      ShareContext::fromPayload($payload['context'] ?? [], $shareable),
    );
  }
}
