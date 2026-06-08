<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\ChunkedUpload\Exception;

final class InvalidUploadTokenException extends \RuntimeException {
  public function __construct(string $message = 'Invalid upload token.') {
    parent::__construct($message);
  }
}
