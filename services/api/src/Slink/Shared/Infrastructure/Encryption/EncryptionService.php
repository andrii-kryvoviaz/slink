<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Encryption;

use SensitiveParameter;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class EncryptionService {
  private const string ENCRYPTION_PREFIX = 'enc:v1:';
  
  private string $encryptionKey;
  
  public function __construct(
    #[Autowire('%env(APP_SECRET)%')]
    #[SensitiveParameter]
    string $appSecret
  ) {
    $this->encryptionKey = sodium_crypto_generichash($appSecret, '', SODIUM_CRYPTO_SECRETBOX_KEYBYTES);
  }
  
  public function encrypt(#[SensitiveParameter] string $plaintext): string {
    if ($this->isEncrypted($plaintext)) {
      return $plaintext;
    }
    
    $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
    $ciphertext = sodium_crypto_secretbox($plaintext, $nonce, $this->encryptionKey);
    
    return self::ENCRYPTION_PREFIX . base64_encode($nonce . $ciphertext);
  }
  
  public function decrypt(#[SensitiveParameter] string $value): string {
    if (!$this->isEncrypted($value)) {
      return $value;
    }
    
    $encoded = substr($value, strlen(self::ENCRYPTION_PREFIX));
    $decoded = base64_decode($encoded, true);
    
    if ($decoded === false) {
      throw new \RuntimeException('Failed to decode encrypted value');
    }
    
    $nonce = substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
    $ciphertext = substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
    
    $plaintext = sodium_crypto_secretbox_open($ciphertext, $nonce, $this->encryptionKey);
    
    if ($plaintext === false) {
      throw new \RuntimeException('Failed to decrypt value');
    }
    
    return $plaintext;
  }
  
  public function isEncrypted(string $value): bool {
    return str_starts_with($value, self::ENCRYPTION_PREFIX);
  }
}
