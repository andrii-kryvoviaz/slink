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
    return self::$service?->encrypt($plaintext) ?? $plaintext;
  }
  
  public static function decrypt(#[SensitiveParameter] string $value): string {
    return self::$service?->decrypt($value) ?? $value;
  }
  
  public static function isAvailable(): bool {
    return self::$service !== null;
  }
}
