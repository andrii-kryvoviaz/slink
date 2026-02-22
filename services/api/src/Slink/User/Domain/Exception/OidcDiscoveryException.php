<?php

declare(strict_types=1);

namespace Slink\User\Domain\Exception;

final class OidcDiscoveryException extends \RuntimeException {
  public static function httpError(string $discoveryUrl, \Throwable $previous): self {
    return new self(
      sprintf('Failed to fetch OIDC discovery document from "%s": %s', $discoveryUrl, $previous->getMessage()),
      0,
      $previous,
    );
  }

  /**
   * @param array<string> $missingKeys
   */
  public static function missingKeys(string $discoveryUrl, array $missingKeys): self {
    return new self(
      sprintf(
        'OIDC discovery document from "%s" is missing required keys: %s',
        $discoveryUrl,
        implode(', ', $missingKeys),
      ),
    );
  }
}
