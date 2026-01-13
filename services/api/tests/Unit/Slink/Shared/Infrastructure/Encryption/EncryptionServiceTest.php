<?php

declare(strict_types=1);

namespace Unit\Slink\Shared\Infrastructure\Encryption;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Infrastructure\Encryption\EncryptionService;

final class EncryptionServiceTest extends TestCase {
  private EncryptionService $service;
  
  protected function setUp(): void {
    $this->service = new EncryptionService('test-app-secret-key-for-testing');
  }
  
  #[Test]
  public function itEncryptsAndDecryptsValue(): void {
    $plaintext = 'my-secret-password';
    
    $encrypted = $this->service->encrypt($plaintext);
    $decrypted = $this->service->decrypt($encrypted);
    
    $this->assertNotSame($plaintext, $encrypted);
    $this->assertSame($plaintext, $decrypted);
  }
  
  #[Test]
  public function itReturnsEncryptedValueWithPrefix(): void {
    $encrypted = $this->service->encrypt('test');
    
    $this->assertTrue($this->service->isEncrypted($encrypted));
    $this->assertStringStartsWith('enc:v1:', $encrypted);
  }
  
  #[Test]
  public function itDoesNotDoubleEncrypt(): void {
    $plaintext = 'test-value';
    
    $encrypted1 = $this->service->encrypt($plaintext);
    $encrypted2 = $this->service->encrypt($encrypted1);
    
    $this->assertSame($encrypted1, $encrypted2);
  }
  
  #[Test]
  public function itDecryptsPlaintextWithoutPrefix(): void {
    $plaintext = 'not-encrypted-value';
    
    $result = $this->service->decrypt($plaintext);
    
    $this->assertSame($plaintext, $result);
  }
  
  #[Test]
  public function itHandlesEmptyString(): void {
    $encrypted = $this->service->encrypt('');
    $decrypted = $this->service->decrypt($encrypted);
    
    $this->assertSame('', $decrypted);
  }
  
  #[Test]
  public function itHandlesSpecialCharacters(): void {
    $plaintext = 'p@$$w0rd!@#$%^&*()_+{}|:"<>?`~';
    
    $encrypted = $this->service->encrypt($plaintext);
    $decrypted = $this->service->decrypt($encrypted);
    
    $this->assertSame($plaintext, $decrypted);
  }
  
  #[Test]
  public function itProducesDifferentCiphertextForSamePlaintext(): void {
    $plaintext = 'same-value';
    
    $encrypted1 = $this->service->encrypt($plaintext);
    $service2 = new EncryptionService('test-app-secret-key-for-testing');
    $encrypted2 = $service2->encrypt($plaintext);
    
    $this->assertNotSame($encrypted1, $encrypted2);
    $this->assertSame($plaintext, $this->service->decrypt($encrypted1));
    $this->assertSame($plaintext, $service2->decrypt($encrypted2));
  }
  
  #[Test]
  public function itDecryptsValueFromDifferentInstance(): void {
    $plaintext = 'cross-instance-test';
    
    $service1 = new EncryptionService('shared-secret');
    $encrypted = $service1->encrypt($plaintext);
    
    $service2 = new EncryptionService('shared-secret');
    $decrypted = $service2->decrypt($encrypted);
    
    $this->assertSame($plaintext, $decrypted);
  }
}
