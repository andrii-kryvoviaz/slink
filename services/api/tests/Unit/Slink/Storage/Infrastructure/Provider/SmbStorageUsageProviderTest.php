<?php

declare(strict_types=1);

namespace Unit\Slink\Storage\Infrastructure\Provider;

use Icewind\SMB\Exception\AuthenticationException;
use Icewind\SMB\Exception\ConnectException;
use Icewind\SMB\Exception\ConnectionRefusedException;
use Icewind\SMB\IShare;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Domain\Enum\StorageProvider;
use Slink\Storage\Domain\Exception\SmbConfigurationIncompleteException;
use Slink\Storage\Infrastructure\Provider\SmbShareFactoryInterface;
use Slink\Storage\Infrastructure\Provider\SmbStorageUsageProvider;

final class SmbStorageUsageProviderTest extends TestCase {
    private ConfigurationProviderInterface $configurationProvider;
    private SmbStorageUsageProvider $provider;

    protected function setUp(): void {
        $this->configurationProvider = $this->createStub(ConfigurationProviderInterface::class);
        $this->provider = new SmbStorageUsageProvider($this->configurationProvider);
    }

    #[Test]
    public function itSupportsSmbProvider(): void {
        $this->assertTrue($this->provider->supports(StorageProvider::SmbShare));
        $this->assertFalse($this->provider->supports(StorageProvider::Local));
        $this->assertFalse($this->provider->supports(StorageProvider::AmazonS3));
    }

    #[Test]
    public function itReturnsCorrectAlias(): void {
        $this->assertSame('smb', SmbStorageUsageProvider::getAlias());
    }

    #[Test]
    public function itThrowsExceptionWhenConfigurationIsNull(): void {
        $configurationProvider = $this->createMock(ConfigurationProviderInterface::class);
        $configurationProvider
            ->expects($this->once())
            ->method('get')
            ->with('storage.adapter.smb')
            ->willReturn(null);

        $provider = new SmbStorageUsageProvider($configurationProvider);

        $this->expectException(SmbConfigurationIncompleteException::class);

        $provider->getUsage();
    }

    #[Test]
    public function itThrowsExceptionWhenHostIsMissing(): void {
        $config = [
            'share' => 'myshare',
            'username' => 'user'
        ];

        $configurationProvider = $this->createMock(ConfigurationProviderInterface::class);
        $configurationProvider
            ->expects($this->once())
            ->method('get')
            ->with('storage.adapter.smb')
            ->willReturn($config);

        $provider = new SmbStorageUsageProvider($configurationProvider);

        $this->expectException(SmbConfigurationIncompleteException::class);

        $provider->getUsage();
    }

    #[Test]
    public function itThrowsExceptionWhenShareIsMissing(): void {
        $config = [
            'host' => 'example.com',
            'username' => 'user'
        ];

        $configurationProvider = $this->createMock(ConfigurationProviderInterface::class);
        $configurationProvider
            ->expects($this->once())
            ->method('get')
            ->with('storage.adapter.smb')
            ->willReturn($config);

        $provider = new SmbStorageUsageProvider($configurationProvider);

        $this->expectException(SmbConfigurationIncompleteException::class);

        $provider->getUsage();
    }

    #[Test]
    public function itThrowsExceptionWhenUsernameIsMissing(): void {
        $config = [
            'host' => 'example.com',
            'share' => 'myshare'
        ];

        $configurationProvider = $this->createMock(ConfigurationProviderInterface::class);
        $configurationProvider
            ->expects($this->once())
            ->method('get')
            ->with('storage.adapter.smb')
            ->willReturn($config);

        $provider = new SmbStorageUsageProvider($configurationProvider);

        $this->expectException(SmbConfigurationIncompleteException::class);

        $provider->getUsage();
    }

    #[Test]
    public function itMapsAuthenticationFailureToConfigurationError(): void {
        $provider = $this->providerForFailingShare(
            new AuthenticationException('Authentication failed')
        );

        $this->expectException(SmbConfigurationIncompleteException::class);

        $provider->getUsage();
    }

    #[Test]
    public function itMapsConnectFailureToConfigurationError(): void {
        $provider = $this->providerForFailingShare(
            new ConnectionRefusedException('Connection refused')
        );

        $this->expectException(SmbConfigurationIncompleteException::class);

        $provider->getUsage();
    }

    private function providerForFailingShare(ConnectException $failure): SmbStorageUsageProvider {
        $config = [
            'host' => 'smb.example.test',
            'share' => 'slink',
            'username' => 'user',
            'password' => 'password',
        ];

        $configurationProvider = $this->createStub(ConfigurationProviderInterface::class);
        $configurationProvider
            ->method('get')
            ->willReturnMap([
                ['storage.adapter.smb', $config],
                ['storage.adapter.path', 'slink'],
            ]);

        $share = $this->createStub(IShare::class);
        $share->method('stat')->willThrowException($failure);
        $share->method('dir')->willThrowException($failure);

        $shareFactory = $this->createStub(SmbShareFactoryInterface::class);
        $shareFactory->method('create')->willReturn($share);

        return new SmbStorageUsageProvider($configurationProvider, $shareFactory);
    }
}
