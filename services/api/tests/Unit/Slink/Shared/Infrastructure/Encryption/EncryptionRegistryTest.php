<?php

declare(strict_types=1);

namespace Unit\Slink\Shared\Infrastructure\Encryption;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Infrastructure\Encryption\EncryptionRegistry;
use Slink\Shared\Infrastructure\Encryption\EncryptionService;

final class EncryptionRegistryTest extends TestCase {
  #[Test]
  public function itThrowsWhenEncryptingWithoutService(): void {
    $reflection = new \ReflectionClass(EncryptionRegistry::class);
    $property = $reflection->getProperty('service');
    $property->setValue(null, null);

    $this->assertFalse(EncryptionRegistry::isAvailable());

    $this->expectException(\RuntimeException::class);
    $this->expectExceptionMessage('EncryptionService not initialized');

    EncryptionRegistry::encrypt('test-value');
  }

  #[Test]
  public function itThrowsWhenDecryptingWithoutService(): void {
    $reflection = new \ReflectionClass(EncryptionRegistry::class);
    $property = $reflection->getProperty('service');
    $property->setValue(null, null);

    $this->assertFalse(EncryptionRegistry::isAvailable());

    $this->expectException(\RuntimeException::class);
    $this->expectExceptionMessage('EncryptionService not initialized');

    EncryptionRegistry::decrypt('test-value');
  }
  
  #[Test]
  public function itUsesServiceWhenSet(): void {
    $service = new EncryptionService('test-secret');
    EncryptionRegistry::setService($service);
    
    $plaintext = 'test-value';
    $encrypted = EncryptionRegistry::encrypt($plaintext);
    
    $this->assertTrue(EncryptionRegistry::isAvailable());
    $this->assertNotSame($plaintext, $encrypted);
    $this->assertSame($plaintext, EncryptionRegistry::decrypt($encrypted));
  }
}
