<?php

declare(strict_types=1);

namespace Slink\Image\Domain\ValueObject;

use Slink\Shared\Domain\Exception\DateTimeException;
use Slink\Shared\Domain\ValueObject\AbstractCompoundValueObject;
use Slink\Shared\Domain\ValueObject\DateTime;
use Doctrine\ORM\Mapping as ORM;
use Slink\Shared\Domain\ValueObject\MutableValueObject;

#[ORM\Embeddable]
final readonly class ImageAttributes extends AbstractCompoundValueObject {
  use MutableValueObject;
  
  /**
   * @param string $fileName
   * @param string $description
   * @param bool $isPublic
   * @param DateTime $createdAt
   * @param DateTime|null $updatedAt
   * @param int|null $views
   */
  private function __construct(
    #[ORM\Column(type: 'string')]
    private string $fileName,
    
    #[ORM\Column(type: 'string')]
    private string $description,
    
    #[ORM\Column(type: 'boolean')]
    private bool $isPublic,
    
    #[ORM\Column(type: 'datetime_immutable')]
    private DateTime $createdAt,
    
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTime $updatedAt,
    
    #[ORM\Column(type: 'integer')]
    private ?int $views = 0,
  ) {
  }
  
  /**
   * @param string $fileName
   * @param string $description
   * @param bool $isPublic
   * @param DateTime|null $createdAt
   * @param DateTime|null $updatedAt
   * @param int|null $views
   * @return self
   * @throws DateTimeException
   */
  public static function create(string $fileName, string $description, bool $isPublic, ?DateTime $createdAt = null, ?DateTime $updatedAt = null, ?int $views = 0): self {
    return new self($fileName, $description, $isPublic, $createdAt ?? DateTime::now(), $updatedAt, $views);
  }
  
  /**
   * @return string
   */
  public function getFileName(): string {
    return $this->fileName;
  }
  
  /**
   * @return string
   */
  public function getDescription(): string {
    return $this->description;
  }
  
  /**
   * @return bool
   */
  public function isPublic(): bool {
    return $this->isPublic;
  }
  
  /**
   * @return DateTime
   */
  public function getCreatedAt(): DateTime {
    return $this->createdAt;
  }
  
  public function getUpdatedAt(): ?DateTime {
    return $this->updatedAt;
  }
  
  public function getViews(): ?int {
    return $this->views;
  }
  
  public function getExtension(): string {
    return pathinfo($this->fileName, PATHINFO_EXTENSION);
  }
  
  public function addView(): self {
    return new self(
      $this->fileName,
      $this->description,
      $this->isPublic,
      $this->createdAt,
      $this->updatedAt,
      $this->views + 1,
    );
  }
  
  public function toPayload(): array {
    return [
      'fileName' => $this->fileName,
      'description' => $this->description,
      'isPublic' => $this->isPublic,
      'createdAt' => $this->createdAt->toString(),
      'updatedAt' => $this->updatedAt?->toString(),
      'views' => $this->views,
    ];
  }
  
  /**
   * @throws DateTimeException
   */
  public static function fromPayload(array $payload): static {
    return new self(
      $payload['fileName'],
      $payload['description'],
      $payload['isPublic'],
      DateTime::fromString($payload['createdAt']),
      isset($payload['updatedAt']) ? DateTime::fromString($payload['updatedAt']) : null,
      $payload['views'],
    );
  }
  
  public function withDescription(?string $description): self {
    return $this->markForUpdate('description', $description);
  }

  public function withIsPublic(?bool $isPublic): self {
    return $this->markForUpdate('isPublic', $isPublic);
  }
}