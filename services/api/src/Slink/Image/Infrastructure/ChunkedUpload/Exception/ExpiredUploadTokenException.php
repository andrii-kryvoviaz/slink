<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\ChunkedUpload\Exception;

final class ExpiredUploadTokenException extends \RuntimeException {
  public function __construct(string $message = 'The upload token has expired.') {
    parent::__construct($message);
  }
}
