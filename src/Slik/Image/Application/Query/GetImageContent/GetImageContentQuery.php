<?php

declare(strict_types=1);

namespace Slik\Image\Application\Query\GetImageContent;

use Slik\Shared\Application\Query\QueryInterface;
use Slik\Shared\Domain\ValueObject\ImageOptions;

final class GetImageContentQuery implements QueryInterface {
  private ?ImageOptions $imageOptions = null;
  
  public function __construct(
    string $fileName,
    string $mimeType,
    ?string $width = null,
    ?string $height = null
  ) {
    $this->imageOptions = ImageOptions::fromPayload([
      'fileName' => $fileName,
      'mimeType' => $mimeType,
      'width' => $width ? (int) $width : null,
      'height' => $height ? (int) $height : null,
    ]);
  }
  
  public function getImageOptions(): ImageOptions {
    return $this->imageOptions;
  }
}