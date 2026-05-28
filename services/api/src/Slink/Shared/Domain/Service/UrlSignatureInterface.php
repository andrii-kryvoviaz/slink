<?php

declare(strict_types=1);

namespace Slink\Shared\Domain\Service;

interface UrlSignatureInterface {
  /**
   * @param array<string, mixed> $payload
   */
  public function sign(string $scope, array $payload = []): string;

  /**
   * @param array<string, mixed> $payload
   */
  public function verify(string $scope, array $payload, string $signature): bool;
}
