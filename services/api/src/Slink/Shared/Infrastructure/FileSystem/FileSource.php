<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\FileSystem;

use InvalidArgumentException;
use RuntimeException;
use Slink\Image\Domain\Service\ImageSource;

final readonly class FileSource implements ImageSource {
  private function __construct(
    private ?string $localPath,
    private ?FileStream $stream
  ) {
  }

  public static function fromLocalPath(string $localPath): self {
    return new self($localPath, null);
  }

  public static function fromStream(FileStream $stream): self {
    return new self(null, $stream);
  }

  public function hasLocalPath(): bool {
    return $this->localPath !== null;
  }

  public function getLocalPath(): string {
    if ($this->localPath === null) {
      throw new InvalidArgumentException('File source has no local path');
    }

    return $this->localPath;
  }

  public function getStream(): FileStream {
    if ($this->stream === null) {
      throw new InvalidArgumentException('File source has no stream');
    }

    return $this->stream;
  }

  public function readBytes(): string {
    if ($this->localPath !== null) {
      return $this->readLocalBytes($this->localPath);
    }

    return $this->readStreamBytes();
  }

  private function readLocalBytes(string $localPath): string {
    $bytes = \file_get_contents($localPath);

    if ($bytes === false) {
      throw new RuntimeException('Failed to read image source from local path');
    }

    return $bytes;
  }

  private function readStreamBytes(): string {
    $bytes = \stream_get_contents($this->getStream()->resource());

    if ($bytes === false) {
      throw new RuntimeException('Failed to read image source from stream');
    }

    return $bytes;
  }
}
