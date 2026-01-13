<?php

declare(strict_types=1);

namespace Slink\Settings\Domain\ValueObject\Storage;

use Slink\Settings\Domain\Exception\S3BucketNotConfiguredException;
use Slink\Settings\Domain\Exception\S3CredentialsNotConfiguredException;
use Slink\Settings\Domain\Exception\S3RegionNotConfiguredException;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use SensitiveParameter;
use Slink\Shared\Domain\ValueObject\AbstractCompoundValueObject;

final readonly class AmazonS3StorageSettings extends AbstractCompoundValueObject {
  /**
   * @param string $region
   * @param string $bucket
   * @param string $key
   * @param string $secret
   * @param string|null $endpoint
   * @param bool $useCustomProvider
   * @param bool $forcePathStyle
   */
  private function __construct(
    private string $region,
    private string $bucket,
    
    #[SensitiveParameter]
    private string $key,
    
    #[SensitiveParameter]
    private string $secret,
    
    private ?string $endpoint = null,
    private bool $useCustomProvider = false,
    private bool $forcePathStyle = false
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
   * @return bool
   */
  public function usesCustomProvider(): bool {
    return $this->useCustomProvider;
  }

  /**
   * @return bool
   */
  public function isForcePathStyle(): bool {
    return $this->forcePathStyle;
  }
  
  /**
   * @param array{
   *   region?: string,
   *   bucket?: string,
   *   key?: string,
   *   secret?: string,
   *   endpoint?: string|null,
   *   useCustomProvider?: bool|null,
   *   forcePathStyle?: bool|null
   * } $payload
   * @return static
   */
  #[\Override]
  public static function fromPayload(array $payload): static {
    $endpoint = $payload['endpoint'] ?? null;
    $useCustomProvider = $payload['useCustomProvider'] ?? (bool) $endpoint;
    $region = trim((string) ($payload['region'] ?? ''));
    $bucket = trim((string) ($payload['bucket'] ?? ''));
    $key = trim((string) ($payload['key'] ?? ''));
    $secret = trim((string) ($payload['secret'] ?? ''));
    $forcePathStyle = (bool) ($payload['forcePathStyle'] ?? false);

    if (!$useCustomProvider && empty($region)) {
      throw new S3RegionNotConfiguredException();
    }

    if (empty($bucket)) {
      throw new S3BucketNotConfiguredException();
    }

    if (empty($key)) {
      throw new S3CredentialsNotConfiguredException('key');
    }

    if (empty($secret)) {
      throw new S3CredentialsNotConfiguredException('secret');
    }

    return new self(
      $region,
      $bucket,
      $key,
      $secret,
      $endpoint,
      $useCustomProvider,
      $forcePathStyle
    );
  }
  
  public static function fromConfig(ConfigurationProviderInterface $configurationProvider): static {
    return self::fromPayload([
      'region' => $configurationProvider->get('storage.adapter.s3.region'),
      'bucket' => $configurationProvider->get('storage.adapter.s3.bucket'),
      'key' => $configurationProvider->get('storage.adapter.s3.key'),
      'secret' => $configurationProvider->get('storage.adapter.s3.secret'),
      'endpoint' => $configurationProvider->get('storage.adapter.s3.endpoint'),
      'useCustomProvider' => $configurationProvider->get('storage.adapter.s3.useCustomProvider'),
      'forcePathStyle' => $configurationProvider->get('storage.adapter.s3.forcePathStyle'),
    ]);
  }
  
  /**
   * @return array{
   *   region: string,
   *   bucket: string,
   *   key: string,
   *   secret: string,
   *   endpoint: string|null,
   *   useCustomProvider: bool,
   *   forcePathStyle: bool
   * }
   */
  #[\Override]
  public function toPayload(): array {
    return [
      'region' => $this->region,
      'bucket' => $this->bucket,
      'key' => $this->key,
      'secret' => $this->secret,
      'endpoint' => $this->endpoint,
      'useCustomProvider' => $this->useCustomProvider,
      'forcePathStyle' => $this->forcePathStyle
    ];
  }
  
  /**
   * @return array<string, mixed>
   */
  public function toClientConfig(): array {
    $region = $this->region;
    
    if ($this->useCustomProvider && empty($region)) {
      $region = 'auto';
    }
    
    $config = [
      'version' => 'latest',
      'region' => $region,
      'credentials' => [
        'key' => $this->key,
        'secret' => $this->secret,
      ],
    ];
    
    if (!$this->useCustomProvider) {
      return $config;
    }
    
    if ($this->endpoint) {
      $config['endpoint'] = $this->endpoint;
    }
    
    $config['use_path_style_endpoint'] = $this->forcePathStyle;
    
    return $config;
  }
}
