<?php

declare(strict_types=1);

namespace Slink\Share\Infrastructure\Service;

use Slink\Image\Domain\Service\ImageUrlSignatureInterface;
use Slink\Share\Domain\Service\ShareUrlBuilderInterface;

final readonly class ShareUrlBuilder implements ShareUrlBuilderInterface {
  public function __construct(
    private ImageUrlSignatureInterface $signatureService,
  ) {
  }

  public function buildTargetUrl(string $imageId, string $fileName, ?int $width, ?int $height, bool $crop, ?string $format = null, ?string $filter = null): string {
    $targetFileName = $format && $format !== 'original' 
      ? $this->applyFormat($fileName, $format) 
      : $fileName;
    $params = $this->buildParams($width, $height, $crop, $filter);
    $url = "/image/{$targetFileName}";

    if (empty($params)) {
      return $url;
    }

    $signature = $this->signatureService->sign($imageId, $params);
    $queryParams = [...$params, 's' => $signature];

    return "{$url}?" . http_build_query($queryParams);
  }

  private function applyFormat(string $fileName, string $format): string {
    $baseName = pathinfo($fileName, PATHINFO_FILENAME);
    
    return "{$baseName}.{$format}";
  }

  /**
   * @return array<string, int|bool|string>
   */
  private function buildParams(?int $width, ?int $height, bool $crop, ?string $filter = null): array {
    return array_filter([
      'width' => $width,
      'height' => $height,
      'crop' => $crop,
      'filter' => $filter,
    ], fn($value) => $value !== null && $value !== false);
  }
}
