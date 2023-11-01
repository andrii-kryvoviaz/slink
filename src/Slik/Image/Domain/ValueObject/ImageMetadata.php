<?php

declare(strict_types=1);

namespace Slik\Image\Domain\ValueObject;

use Doctrine\ORM\Mapping as ORM;
use Slik\Shared\Domain\Exception\DateTimeException;
use Slik\Shared\Domain\ValueObject\AbstractCompoundValueObject;
use Slik\Shared\Domain\ValueObject\DateTime;

#[ORM\Embeddable]
final readonly class ImageMetadata extends AbstractCompoundValueObject{
  
  public function __construct(
    #[ORM\Column(type: 'string')]
    private string $originalName,
    
    #[ORM\Column(type: 'datetime_immutable')]
    private DateTime $fileDateTime,
    
    #[ORM\Column(type: 'integer')]
    private int $fileSize,
    
    #[ORM\Column(type: 'string')]
    private string $mimeType,
    
    #[ORM\Column(type: 'integer')]
    private int $width,
    
    #[ORM\Column(type: 'integer')]
    private int $height,
    
    #[ORM\Column(type: 'boolean')]
    private bool $isColor,
  ) {
  }
  
  public function toPayload(): array {
    return [
      'originalName' => $this->originalName,
      'fileDateTime' => $this->fileDateTime->toString(),
      'fileSize' => $this->fileSize,
      'mimeType' => $this->mimeType,
      'width' => $this->width,
      'height' => $this->height,
      'isColor' => $this->isColor,
    ];
  }
  
  /**
   * @throws DateTimeException
   */
  public static function fromPayload(array $payload): static {
    return new self(
      $payload['originalName'],
      DateTime::fromString($payload['fileDateTime']),
      $payload['fileSize'],
      $payload['mimeType'],
      $payload['width'],
      $payload['height'],
      $payload['isColor'],
    );
  }
  
  /**
   * @throws DateTimeException
   */
  public static function fromExifData(array $exifData): static {
    return new self(
      $exifData['FileName'],
      DateTime::fromTimeStamp($exifData['FileDateTime']),
      $exifData['FileSize'],
      $exifData['MimeType'],
      $exifData['COMPUTED']['Width'],
      $exifData['COMPUTED']['Height'],
      (bool) $exifData['COMPUTED']['IsColor'],
    );
  }
  
  public function getOriginalName(): string {
    return $this->originalName;
  }
  
  public function getFileDateTime(): DateTime {
    return $this->fileDateTime;
  }
  
  public function getFileSize(): int {
    return $this->fileSize;
  }
  
  public function getMimeType(): string {
    return $this->mimeType;
  }
  
  public function getWidth(): int {
    return $this->width;
  }
  
  public function getHeight(): int {
    return $this->height;
  }
  
  public function isColor(): bool {
    return $this->isColor;
  }
}