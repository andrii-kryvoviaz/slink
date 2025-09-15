<?php

declare(strict_types=1);

namespace Slink\Image\Domain\ValueObject;

use Slink\Shared\Domain\ValueObject\AbstractValueObject;

final readonly class ImageFile extends AbstractValueObject {
  public function __construct(
    private string  $pathname,
    private ?string $mimeType = null,
    private ?string $extension = null,
    private ?int    $size = null
  ) {
  }

  public static function fromSymfonyFile(\Symfony\Component\HttpFoundation\File\File $file): self {
    return new self(
      pathname: $file->getPathname(),
      mimeType: $file->getMimeType(),
      extension: $file->guessExtension(),
      size: $file->getSize() ?: null
    );
  }

  public function getPathname(): string {
    return $this->pathname;
  }

  public function getMimeType(): ?string {
    return $this->mimeType;
  }

  public function getExtension(): ?string {
    return $this->extension;
  }

  public function getSize(): ?int {
    return $this->size;
  }

  protected function toNative(): string {
    return $this->pathname;
  }
}