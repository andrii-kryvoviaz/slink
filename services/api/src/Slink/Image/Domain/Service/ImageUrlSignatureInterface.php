<?php

declare(strict_types=1);

namespace Slink\Image\Domain\Service;

interface ImageUrlSignatureInterface {
  /**
   * @param array<string, mixed> $params
   */
  public function sign(string $imageId, array $params = []): string;
  
  /**
   * @param array<string, mixed> $params
   */
  public function verify(string $imageId, array $params, string $signature): bool;
}
