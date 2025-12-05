<?php

declare(strict_types=1);

namespace Slink\Image\Domain\ValueObject;

use Doctrine\ORM\Mapping as ORM;
use Slink\Shared\Domain\ValueObject\AbstractCompoundValueObject;
use Slink\Shared\Infrastructure\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;

#[ORM\Embeddable]
final readonly class ImageMetadata extends AbstractCompoundValueObject {

  /**
   * @param int $size
   * @param string $mimeType
   * @param int $width
   * @param int $height
   * @param string|null $sha1Hash
   */
  public function __construct(
    #[ORM\Column(type: 'integer')]
    #[Groups(['public'])]
    private int     $size,

    #[ORM\Column(type: 'string')]
    #[Groups(['public'])]
    #[SerializedName('mimeType')]
    private string  $mimeType,

    #[ORM\Column(type: 'integer')]
    #[Groups(['public'])]
    private int     $width,

    #[ORM\Column(type: 'integer')]
    #[Groups(['public'])]
    private int     $height,

    #[ORM\Column(type: 'string', length: 40, nullable: true)]
    #[SerializedName('sha1Hash')]
    private ?string $sha1Hash = null,
  ) {
  }

  /**
   * @return int
   */
  public function getSize(): int {
    return $this->size;
  }

  /**
   * @return string
   */
  public function getMimeType(): string {
    return $this->mimeType;
  }

  /**
   * @return int
   */
  public function getWidth(): int {
    return $this->width;
  }

  /**
   * @return int
   */
  public function getHeight(): int {
    return $this->height;
  }

  /**
   * @return string|null
   */
  public function getSha1Hash(): ?string {
    return $this->sha1Hash;
  }

  /**
   * @param string $sha1Hash
   * @return self
   */
  public function withHash(string $sha1Hash): self {
    return new self(
      $this->size,
      $this->mimeType,
      $this->width,
      $this->height,
      $sha1Hash
    );
  }

  /**
   * @return array|mixed[]
   */
  public function toPayload(): array {
    return [
      'size' => $this->size,
      'mimeType' => $this->mimeType,
      'width' => $this->width,
      'height' => $this->height,
      'sha1Hash' => $this->sha1Hash,
    ];
  }

  /**
   * @param array<string, mixed> $payload
   * @return static
   */
  public static function fromPayload(array $payload): static {
    return new self(
      $payload['size'],
      $payload['mimeType'],
      $payload['width'],
      $payload['height'],
      $payload['sha1Hash'] ?? null,
    );
  }
}