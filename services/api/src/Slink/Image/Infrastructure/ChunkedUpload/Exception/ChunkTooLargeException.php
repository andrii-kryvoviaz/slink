<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\ChunkedUpload\Exception;

final class ChunkTooLargeException extends \RuntimeException {
  public function __construct(string $message = 'The uploaded chunk exceeds the maximum allowed chunk size.') {
    parent::__construct($message);
  }
}
