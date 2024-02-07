<?php

declare(strict_types=1);

namespace Slik\Image\Domain\Event;

use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\Serialization\SerializablePayload;
use Slik\Image\Domain\ValueObject\ImageAttributes;
use Slik\Shared\Domain\Exception\DateTimeException;
use Slik\Shared\Domain\ValueObject\ID;

final readonly class ImageAttributesWasUpdated implements SerializablePayload{
  /**
   * @param ID|AggregateRootId $id
   * @param ImageAttributes $attributes
   */
  public function __construct(
    public ID|AggregateRootId $id,
    public ImageAttributes $attributes,
  ) {
  }
  
  /**
   * @return array<string, mixed>
   */
  public function toPayload(): array {
    return [
      'id' => $this->id->toString(),
      'attributes' => $this->attributes->toPayload(),
    ];
  }
  
  /**
   * @param array<string, mixed> $payload
   * @return static
   * @throws DateTimeException
   */
  public static function fromPayload(array $payload): static {
    return new self(
      ID::fromString($payload['id']),
      ImageAttributes::fromPayload($payload['attributes']),
    );
  }
}