<?php

declare(strict_types=1);

namespace Slink\Image\Domain\Event;

use EventSauce\EventSourcing\Serialization\SerializablePayload;
use Slink\Image\Domain\ValueObject\ImageAttributes;
use Slink\Shared\Domain\Exception\Date\DateTimeException;
use Slink\Shared\Domain\ValueObject\ID;

final readonly class ImageAttributesWasUpdated implements SerializablePayload{
  /**
   * @param ID $id
   * @param ImageAttributes $attributes
   */
  public function __construct(
    public ID $id,
    public ImageAttributes $attributes,
  ) {
  }
  
  /**
   * @return array<string, mixed>
   */
  public function toPayload(): array {
    return [
      'uuid' => $this->id->toString(),
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
      ID::fromString($payload['uuid']),
      ImageAttributes::fromPayload($payload['attributes']),
    );
  }
}