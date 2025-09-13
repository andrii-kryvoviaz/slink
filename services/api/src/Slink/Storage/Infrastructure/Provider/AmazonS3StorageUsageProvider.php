<?php

declare(strict_types=1);

namespace Slink\Storage\Infrastructure\Provider;

use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Domain\Enum\StorageProvider;
use Slink\Storage\Domain\Exception\S3BucketNotConfiguredException;
use Slink\Storage\Domain\Exception\StorageUsageMetricsDisabledException;
use Slink\Storage\Domain\Service\StorageUsageProviderInterface;
use Slink\Storage\Domain\ValueObject\StorageUsage;

final readonly class AmazonS3StorageUsageProvider implements StorageUsageProviderInterface {
  public function __construct(
    private ConfigurationProviderInterface $configurationProvider
  ) {
  }
  
  public function getUsage(): StorageUsage {
    $bucket = $this->configurationProvider->get('storage.adapter.s3.bucket');
    
    if (!$bucket) {
      throw new S3BucketNotConfiguredException();
    }
    
    throw new StorageUsageMetricsDisabledException('S3 usage calculation disabled to avoid high API costs. Consider using CloudWatch metrics for accurate usage data.');
  }
  
  public function supports(StorageProvider $provider): bool {
    return StorageProvider::AmazonS3->equals($provider);
  }
  
  public static function getAlias(): string {
    return StorageProvider::AmazonS3->value;
  }
}
