<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Encryption;

use SensitiveParameter;

final class EncryptionRegistry {
  private static ?EncryptionService $service = null;
  
  public static function setService(EncryptionService $service): void {
    self::$service = $service;
  }
  
  public static function encrypt(#[SensitiveParameter] string $plaintext): string {
    if (self::$service === null) {
      throw new \RuntimeException('EncryptionService not initialized');
    }

    return self::$service->encrypt($plaintext);
  }
  
  public static function decrypt(#[SensitiveParameter] string $value): string {
    if (self::$service === null) {
      throw new \RuntimeException('EncryptionService not initialized');
    }

    return self::$service->decrypt($value);
  }
  
  public static function isAvailable(): bool {
    return self::$service !== null;
  }
}
