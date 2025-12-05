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

  public function buildTargetUrl(string $imageId, string $fileName, ?int $width, ?int $height, bool $crop): string {
    $params = $this->buildParams($width, $height, $crop);
    $url = '/api/image/' . $fileName;

    if (empty($params)) {
      return $url;
    }

    $signature = $this->signatureService->sign($imageId, $params);
    $queryParams = [...$params, 's' => $signature];

    return $url . '?' . http_build_query($queryParams);
  }

  /**
   * @return array<string, int|bool>
   */
  private function buildParams(?int $width, ?int $height, bool $crop): array {
    return array_filter([
      'width' => $width,
      'height' => $height,
      'crop' => $crop,
    ], fn($value) => $value !== null && $value !== false);
  }
}
