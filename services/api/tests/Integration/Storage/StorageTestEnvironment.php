<?php

declare(strict_types=1);

namespace Tests\Integration\Storage;

use PHPUnit\Framework\Assert;

final class StorageTestEnvironment {
  private const float PROBE_TIMEOUT_SECONDS = 3.0;
  private const float PROBE_RETRY_WINDOW_SECONDS = 20.0;
  private const int PROBE_RETRY_DELAY_MICROSECONDS = 500_000;

  public static function abortBackendNotReachable(string $backendName): never {
    self::abort(sprintf('%s storage backend is not reachable. Storage test backends are not running (make test-storage).', $backendName));
  }

  public static function abort(string $message): never {
    if (self::isCi()) {
      throw new \RuntimeException($message);
    }

    Assert::markTestSkipped($message);
  }

  public static function smbHost(): string {
    return self::env('STORAGE_TEST_SMB_HOST', 'localhost');
  }

  public static function smbShare(): string {
    return self::env('STORAGE_TEST_SMB_SHARE', 'slink');
  }

  public static function smbUsername(): string {
    return self::env('STORAGE_TEST_SMB_USERNAME', 'test');
  }

  public static function smbPassword(): string {
    return self::env('STORAGE_TEST_SMB_PASSWORD', 'test');
  }

  public static function s3Endpoint(): string {
    return self::env('STORAGE_TEST_S3_ENDPOINT', 'http://localhost:8333');
  }

  public static function s3Bucket(): string {
    return self::env('STORAGE_TEST_S3_BUCKET', 'slink-test');
  }

  public static function s3Key(): string {
    return self::env('STORAGE_TEST_S3_KEY', 'slink-test-key');
  }

  public static function s3Secret(): string {
    return self::env('STORAGE_TEST_S3_SECRET', 'slink-test-secret');
  }

  public static function probeTcp(string $host, int $port): bool {
    return self::probeWithRetries(static function () use ($host, $port): bool {
      $socket = @fsockopen($host, $port, $errorCode, $errorMessage, self::PROBE_TIMEOUT_SECONDS);

      if ($socket === false) {
        return false;
      }

      fclose($socket);

      return true;
    });
  }

  public static function probeHttp(string $url): bool {
    return self::probeWithRetries(static function () use ($url): bool {
      $context = stream_context_create([
        'http' => [
          'timeout' => self::PROBE_TIMEOUT_SECONDS,
          'ignore_errors' => true,
        ],
      ]);

      if (@file_get_contents($url, false, $context) === false) {
        return false;
      }

      $statusLine = $http_response_header[0] ?? '';

      return preg_match('/^HTTP\/\S+\s+2\d{2}\b/', $statusLine) === 1;
    });
  }

  private static function probeWithRetries(\Closure $probe): bool {
    $deadline = microtime(true) + self::PROBE_RETRY_WINDOW_SECONDS;

    while (true) {
      if ($probe()) {
        return true;
      }

      if (microtime(true) >= $deadline) {
        return false;
      }

      usleep(self::PROBE_RETRY_DELAY_MICROSECONDS);
    }
  }

  private static function isCi(): bool {
    return filter_var((string) getenv('CI'), FILTER_VALIDATE_BOOLEAN);
  }

  private static function env(string $name, string $default): string {
    $value = getenv($name);

    if ($value === false || $value === '') {
      return $default;
    }

    return $value;
  }
}
