<?php

declare(strict_types=1);

namespace Slink\Image\Domain\Event;

use EventSauce\EventSourcing\Serialization\SerializablePayload;
use Slink\Image\Domain\Enum\License;
use Slink\Shared\Domain\ValueObject\ID;

final readonly class ImageLicenseWasUpdated implements SerializablePayload {
  public function __construct(
    public ID $id,
    public ?License $license,
  ) {
  }

  /**
   * @return array<string, mixed>
   */
  public function toPayload(): array {
    return [
      'uuid' => $this->id->toString(),
      'license' => $this->license?->value,
    ];
  }

  /**
   * @param array<string, mixed> $payload
   */
  public static function fromPayload(array $payload): static {
    $license = isset($payload['license']) && is_string($payload['license'])
      ? License::tryFrom($payload['license'])
      : null;

    return new self(
      ID::fromString($payload['uuid']),
      $license,
    );
  }
}
