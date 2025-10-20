<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\Service;

use Slink\Image\Domain\Service\ImageUrlSignatureInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

#[AsAlias(ImageUrlSignatureInterface::class)]
final readonly class HmacImageUrlSignature implements ImageUrlSignatureInterface {
  public function __construct(
    #[Autowire('%env(APP_SECRET)%')]
    private string $secret
  ) {
  }

  /**
   * @param array<string, mixed> $params
   */
  public function sign(string $imageId, array $params = []): string {
    $payload = $this->buildPayload($imageId, $params);
    return hash_hmac('sha256', $payload, $this->secret);
  }

  /**
   * @param array<string, mixed> $params
   */
  public function verify(string $imageId, array $params, string $signature): bool {
    $expectedSignature = $this->sign($imageId, $params);
    return hash_equals($expectedSignature, $signature);
  }

  /**
   * @param array<string, mixed> $params
   */
  private function buildPayload(string $imageId, array $params): string {
    ksort($params);
    
    $parts = [$imageId];
    foreach ($params as $key => $value) {
      $parts[] = sprintf('%s=%s', $key, $this->normalizeValue($value));
    }
    
    return implode(':', $parts);
  }

  private function normalizeValue(mixed $value): string {
    if ($value === null) {
      return '';
    }
    
    if (is_bool($value)) {
      return $value ? '1' : '0';
    }
    
    return (string)$value;
  }
}
