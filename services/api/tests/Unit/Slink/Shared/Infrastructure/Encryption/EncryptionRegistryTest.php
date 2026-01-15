<?php

declare(strict_types=1);

namespace Unit\Slink\Shared\Infrastructure\Encryption;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Infrastructure\Encryption\EncryptionRegistry;
use Slink\Shared\Infrastructure\Encryption\EncryptionService;

final class EncryptionRegistryTest extends TestCase {
  #[Test]
  public function itReturnsPlaintextWhenServiceNotSet(): void {
    $reflection = new \ReflectionClass(EncryptionRegistry::class);
    $property = $reflection->getProperty('service');
    $property->setValue(null, null);
    
    $plaintext = 'test-value';
    
    $this->assertSame($plaintext, EncryptionRegistry::encrypt($plaintext));
    $this->assertSame($plaintext, EncryptionRegistry::decrypt($plaintext));
    $this->assertFalse(EncryptionRegistry::isAvailable());
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
