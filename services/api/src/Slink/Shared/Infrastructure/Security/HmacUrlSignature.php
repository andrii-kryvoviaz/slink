<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Security;

use Slink\Shared\Domain\Service\UrlSignatureInterface;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsAlias(UrlSignatureInterface::class)]
final readonly class HmacUrlSignature implements UrlSignatureInterface {
  public function __construct(
    #[Autowire('%env(APP_SECRET)%')]
    private string $secret
  ) {
  }

  /**
   * @param array<string, mixed> $payload
   */
  public function sign(string $scope, array $payload = []): string {
    $serialized = $this->buildPayload($scope, $payload);
    return hash_hmac('sha256', $serialized, $this->secret);
  }

  /**
   * @param array<string, mixed> $payload
   */
  public function verify(string $scope, array $payload, string $signature): bool {
    $expectedSignature = $this->sign($scope, $payload);
    return hash_equals($expectedSignature, $signature);
  }

  /**
   * @param array<string, mixed> $payload
   */
  private function buildPayload(string $scope, array $payload): string {
    ksort($payload);

    $parts = [$scope];
    foreach ($payload as $key => $value) {
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
