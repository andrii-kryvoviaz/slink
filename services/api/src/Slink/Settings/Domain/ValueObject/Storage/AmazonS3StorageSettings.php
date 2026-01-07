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
   * @param string|null $endpoint
   * @param bool|null $forcePathStyle
   */
  private function __construct(
    private string $region,
    private string $bucket,
    
    #[SensitiveParameter]
    private string $key,
    
    #[SensitiveParameter]
    private string $secret,
    
    private ?string $endpoint = null,
    private ?bool $forcePathStyle = null
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
   * @return string|null
   */
  public function getEndpoint(): ?string {
    return $this->endpoint;
  }

  /**
   * @return bool|null
   */
  public function isForcePathStyle(): ?bool {
    return $this->forcePathStyle;
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
      $payload['secret'],
      $payload['endpoint'] ?? null,
      $payload['forcePathStyle'] ?? null
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
      'secret' => $this->secret,
      'endpoint' => $this->endpoint,
      'forcePathStyle' => $this->forcePathStyle
    ];
  }
}
