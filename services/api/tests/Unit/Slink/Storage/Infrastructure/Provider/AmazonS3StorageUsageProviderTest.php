<?php

declare(strict_types=1);

namespace Unit\Slink\Storage\Infrastructure\Provider;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Settings\Domain\Exception\S3BucketNotConfiguredException;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Domain\Enum\StorageProvider;
use Slink\Storage\Domain\Exception\StorageUsageMetricsDisabledException;
use Slink\Storage\Infrastructure\Provider\AmazonS3StorageUsageProvider;

final class AmazonS3StorageUsageProviderTest extends TestCase {
    private ConfigurationProviderInterface $configurationProvider;
    private AmazonS3StorageUsageProvider $provider;

    protected function setUp(): void {
        $this->configurationProvider = $this->createStub(ConfigurationProviderInterface::class);
        $this->provider = new AmazonS3StorageUsageProvider($this->configurationProvider);
    }

    #[Test]
    public function itSupportsS3Provider(): void {
        $this->assertTrue($this->provider->supports(StorageProvider::AmazonS3));
        $this->assertFalse($this->provider->supports(StorageProvider::Local));
        $this->assertFalse($this->provider->supports(StorageProvider::SmbShare));
    }

    #[Test]
    public function itReturnsCorrectAlias(): void {
        $this->assertSame('s3', AmazonS3StorageUsageProvider::getAlias());
    }

    #[Test]
    public function itThrowsExceptionWhenBucketIsNotConfigured(): void {
        $configurationProvider = $this->createMock(ConfigurationProviderInterface::class);
        $configurationProvider
            ->expects($this->once())
            ->method('get')
            ->with('storage.adapter.s3.bucket')
            ->willReturn(null);

        $provider = new AmazonS3StorageUsageProvider($configurationProvider);

        $this->expectException(S3BucketNotConfiguredException::class);

        $provider->getUsage();
    }

    #[Test]
    public function itThrowsExceptionWhenBucketIsEmpty(): void {
        $configurationProvider = $this->createMock(ConfigurationProviderInterface::class);
        $configurationProvider
            ->expects($this->once())
            ->method('get')
            ->with('storage.adapter.s3.bucket')
            ->willReturn('');

        $provider = new AmazonS3StorageUsageProvider($configurationProvider);

        $this->expectException(S3BucketNotConfiguredException::class);

        $provider->getUsage();
    }

    #[Test]
    public function itThrowsMetricsDisabledExceptionWhenBucketIsConfigured(): void {
        $configurationProvider = $this->createMock(ConfigurationProviderInterface::class);
        $configurationProvider
            ->expects($this->once())
            ->method('get')
            ->with('storage.adapter.s3.bucket')
            ->willReturn('my-bucket');

        $provider = new AmazonS3StorageUsageProvider($configurationProvider);

        $this->expectException(StorageUsageMetricsDisabledException::class);
        $this->expectExceptionMessage('S3 usage calculation disabled to avoid high API costs. Consider using CloudWatch metrics for accurate usage data.');

        $provider->getUsage();
    }
}