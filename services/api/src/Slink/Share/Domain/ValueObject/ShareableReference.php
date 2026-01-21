<?php

declare(strict_types=1);

namespace Slink\Share\Domain\ValueObject;

use Doctrine\ORM\Mapping as ORM;
use Slink\Share\Domain\Enum\ShareableType;
use Slink\Shared\Domain\ValueObject\AbstractValueObject;
use Slink\Shared\Domain\ValueObject\ID;

#[ORM\Embeddable]
final readonly class ShareableReference extends AbstractValueObject {
  public function __construct(
    #[ORM\Column(type: 'uuid', name: 'shareable_id')]
    private string $shareableId,

    #[ORM\Column(type: 'string', length: 32, enumType: ShareableType::class, name: 'shareable_type')]
    private ShareableType $shareableType,
  ) {
  }

  public static function forImage(ID $imageId): self {
    return new self($imageId->toString(), ShareableType::Image);
  }

  public static function forCollection(ID $collectionId): self {
    return new self($collectionId->toString(), ShareableType::Collection);
  }

  public function getShareableId(): string {
    return $this->shareableId;
  }

  public function getShareableType(): ShareableType {
    return $this->shareableType;
  }

  public function isImage(): bool {
    return $this->shareableType === ShareableType::Image;
  }

  public function isCollection(): bool {
    return $this->shareableType === ShareableType::Collection;
  }

  public function toPayload(): array {
    return [
      'shareableId' => $this->shareableId,
      'shareableType' => $this->shareableType->value,
    ];
  }

  public static function fromPayload(array $payload): self {
    return new self(
      $payload['shareableId'],
      ShareableType::from($payload['shareableType']),
    );
  }
}
