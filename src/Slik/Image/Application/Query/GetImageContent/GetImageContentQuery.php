<?php

declare(strict_types=1);

namespace Slik\Image\Application\Query\GetImageContent;

use Slik\Shared\Application\Query\QueryInterface;

final readonly class GetImageContentQuery implements QueryInterface {
  public function __construct(
    private string $fileName
  ) {
  }
  
  public function getFileName(): string {
    return $this->fileName;
  }
}