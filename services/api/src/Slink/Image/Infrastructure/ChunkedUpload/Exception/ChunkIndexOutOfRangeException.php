<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\ChunkedUpload\Exception;

final class ChunkIndexOutOfRangeException extends \RuntimeException {
  public function __construct(string $message = 'The chunk index is out of range.') {
    parent::__construct($message);
  }
}
