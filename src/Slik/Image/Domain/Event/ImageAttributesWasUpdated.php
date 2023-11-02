<?php

declare(strict_types=1);

namespace Slik\Image\Domain\Event;

use EventSauce\EventSourcing\Serialization\SerializablePayload;
use Slik\Image\Domain\ValueObject\ImageAttributes;
use Slik\Shared\Domain\Exception\DateTimeException;
use Slik\Shared\Domain\ValueObject\ID;

final readonly class ImageAttributesWasUpdated implements SerializablePayload{
  
  public function __construct(
    public ID $id,
    public ImageAttributes $attributes,
  ) {
  }
  
  public function toPayload(): array {
    return [
      'id' => $this->id->toString(),
      'attributes' => $this->attributes->toPayload(),
    ];
  }
  
  /**
   * @throws DateTimeException
   */
  public static function fromPayload(array $payload): static {
    return new self(
      ID::fromString($payload['id']),
      ImageAttributes::fromPayload($payload['attributes']),
    );
  }
}