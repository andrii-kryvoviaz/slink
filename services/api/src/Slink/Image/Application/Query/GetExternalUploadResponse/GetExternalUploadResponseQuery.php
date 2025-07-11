<?php

declare(strict_types=1);

namespace Slink\Image\Application\Query\GetExternalUploadResponse;

use Slink\Shared\Application\Query\QueryInterface;
use Slink\Shared\Infrastructure\MessageBus\EnvelopedMessage;

final readonly class GetExternalUploadResponseQuery implements QueryInterface {
  use EnvelopedMessage;

  public function __construct(
    private string $imageId,
    private string $fileName
  ) {}

  public function getImageId(): string {
    return $this->imageId;
  }

  public function getFileName(): string {
    return $this->fileName;
  }
}
