<?php

declare(strict_types=1);

namespace Slink\Image\Application\Query\GetImageContent;

use Slink\Shared\Application\Query\QueryInterface;
use Slink\Shared\Domain\ValueObject\ImageOptions;

final class GetImageContentQuery implements QueryInterface {
  /**
   * @var ImageOptions|null
   */
  private ?ImageOptions $imageOptions = null;
  
  /**
   * @param string $fileName
   * @param string $mimeType
   * @param string|null $width
   * @param string|null $height
   */
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
  
  /**
   * @return ImageOptions|null
   */
  public function getImageOptions(): ?ImageOptions {
    return $this->imageOptions;
  }
}