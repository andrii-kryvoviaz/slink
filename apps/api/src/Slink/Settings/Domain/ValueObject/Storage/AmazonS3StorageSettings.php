<?php

declare(strict_types=1);

namespace Slink\Settings\Domain\ValueObject\Storage;

use SensitiveParameter;
use Slink\Shared\Domain\ValueObject\AbstractCompoundValueObject;

final readonly class AmazonS3StorageSettings extends AbstractCompoundValueObject {
  /**
   * @param string $region
   * @param string $bucket
   * @param string $key
   * @param string $secret
   */
  private function __construct(
    private string $region,
    private string $bucket,
    
    #[SensitiveParameter]
    private string $key,
    
    #[SensitiveParameter]
    private string $secret
  ) {}
  
  /**
   * @return string
   */
  public function getRegion(): string {
    return $this->region;
  }
  
  /**
   * @return string
   */
  public function getBucket(): string {
    return $this->bucket;
  }
  
  /**
   * @return string
   */
  public function getKey(): string {
    return $this->key;
  }
  
  /**
   * @return string
   */
  public function getSecret(): string {
    return $this->secret;
  }
  
  /**
   * @param array<string, string> $payload
   * @return static
   */
  #[\Override]
  public static function fromPayload(array $payload): static {
    return new self(
      $payload['region'],
      $payload['bucket'],
      $payload['key'],
      $payload['secret']
    );
  }
  
  /**
   * @return array<string, string>
   */
  #[\Override]
  public function toPayload(): array {
    return [
      'region' => $this->region,
      'bucket' => $this->bucket,
      'key' => $this->key,
      'secret' => $this->secret
    ];
  }
}