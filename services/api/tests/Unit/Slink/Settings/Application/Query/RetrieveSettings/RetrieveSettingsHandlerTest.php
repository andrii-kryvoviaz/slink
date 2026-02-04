<?php

declare(strict_types=1);

namespace Unit\Slink\Settings\Application\Query\RetrieveSettings;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Slink\Settings\Application\Query\RetrieveSettings\RetrieveSettingsHandler;
use Slink\Settings\Application\Query\RetrieveSettings\RetrieveSettingsQuery;
use Slink\Settings\Domain\Enum\ConfigurationProvider;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Settings\Infrastructure\Provider\ConfigurationProviderLocator;

final class RetrieveSettingsHandlerTest extends TestCase {

    #[Test]
    public function itShouldRetrieveSettingsWithDefaultProvider(): void {
        $expectedSettings = [
            'user' => [
                'approvalRequired' => true,
                'allowRegistration' => false
            ],
            'image' => [
                'maxSize' => 5242880
            ]
        ];

        $query = new RetrieveSettingsQuery();

        $configurationProvider = $this->createMock(ConfigurationProviderInterface::class);
        $configurationLocator = $this->createMock(ConfigurationProviderLocator::class);

        $configurationLocator
            ->expects($this->once())
            ->method('get')
            ->with(ConfigurationProvider::Default)
            ->willReturn($configurationProvider);

        $configurationProvider
            ->expects($this->once())
            ->method('all')
            ->willReturn($expectedSettings);

        $handler = new RetrieveSettingsHandler($configurationLocator);
        $result = $handler->__invoke($query);

        $this->assertSame($expectedSettings, $result);
    }

    #[Test]
    public function itShouldRetrieveSettingsWithStoreProvider(): void {
        $expectedSettings = [
            'user' => [
                'approvalRequired' => false,
                'allowRegistration' => true
            ]
        ];

        $query = new RetrieveSettingsQuery('store');

        $configurationProvider = $this->createMock(ConfigurationProviderInterface::class);
        $configurationLocator = $this->createMock(ConfigurationProviderLocator::class);

        $configurationLocator
            ->expects($this->once())
            ->method('get')
            ->with(ConfigurationProvider::Store)
            ->willReturn($configurationProvider);

        $configurationProvider
            ->expects($this->once())
            ->method('all')
            ->willReturn($expectedSettings);

        $handler = new RetrieveSettingsHandler($configurationLocator);
        $result = $handler->__invoke($query);

        $this->assertSame($expectedSettings, $result);
    }

    #[Test]
    public function itShouldHandleEmptySettings(): void {
        $query = new RetrieveSettingsQuery();

        $configurationProvider = $this->createMock(ConfigurationProviderInterface::class);
        $configurationLocator = $this->createMock(ConfigurationProviderLocator::class);

        $configurationLocator
            ->expects($this->once())
            ->method('get')
            ->with(ConfigurationProvider::Default)
            ->willReturn($configurationProvider);

        $configurationProvider
            ->expects($this->once())
            ->method('all')
            ->willReturn([]);

        $handler = new RetrieveSettingsHandler($configurationLocator);
        $result = $handler->__invoke($query);

        $this->assertSame([], $result);
    }

    #[Test]
    public function itShouldThrowExceptionForInvalidProvider(): void {
        $configurationLocator = $this->createStub(ConfigurationProviderLocator::class);
        $handler = new RetrieveSettingsHandler($configurationLocator);

        $query = new RetrieveSettingsQuery('invalid');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid configuration provider');

        $handler->__invoke($query);
    }

    #[Test]
    public function itShouldPropagateContainerException(): void {
        $query = new RetrieveSettingsQuery();

        $configurationLocator = $this->createMock(ConfigurationProviderLocator::class);
        $configurationLocator
            ->expects($this->once())
            ->method('get')
            ->willThrowException(new class extends \Exception implements ContainerExceptionInterface {});

        $this->expectException(ContainerExceptionInterface::class);

        $handler = new RetrieveSettingsHandler($configurationLocator);
        $handler->__invoke($query);
    }

    #[Test]
    public function itShouldPropagateNotFoundException(): void {
        $query = new RetrieveSettingsQuery();

        $configurationLocator = $this->createMock(ConfigurationProviderLocator::class);
        $configurationLocator
            ->expects($this->once())
            ->method('get')
            ->willThrowException(new class extends \Exception implements NotFoundExceptionInterface {});

        $this->expectException(NotFoundExceptionInterface::class);

        $handler = new RetrieveSettingsHandler($configurationLocator);
        $handler->__invoke($query);
    }

    #[Test]
    public function itShouldHandleComplexNestedSettings(): void {
        $expectedSettings = [
            'user' => [
                'approvalRequired' => true,
                'password' => [
                    'minLength' => 8,
                    'complexity' => [
                        'requireUppercase' => true,
                        'requireNumbers' => false
                    ]
                ]
            ],
            'image' => [
                'processing' => [
                    'quality' => 85,
                    'formats' => ['jpeg', 'webp']
                ]
            ]
        ];

        $query = new RetrieveSettingsQuery();

        $configurationProvider = $this->createMock(ConfigurationProviderInterface::class);
        $configurationLocator = $this->createMock(ConfigurationProviderLocator::class);

        $configurationLocator
            ->expects($this->once())
            ->method('get')
            ->with(ConfigurationProvider::Default)
            ->willReturn($configurationProvider);

        $configurationProvider
            ->expects($this->once())
            ->method('all')
            ->willReturn($expectedSettings);

        $handler = new RetrieveSettingsHandler($configurationLocator);
        $result = $handler->__invoke($query);

        $this->assertSame($expectedSettings, $result);
    }

    #[Test]
    public function itShouldHandleProviderReturningNullValues(): void {
        $expectedSettings = [
            'user' => [
                'setting1' => null,
                'setting2' => 'value'
            ]
        ];

        $query = new RetrieveSettingsQuery();

        $configurationProvider = $this->createMock(ConfigurationProviderInterface::class);
        $configurationLocator = $this->createMock(ConfigurationProviderLocator::class);

        $configurationLocator
            ->expects($this->once())
            ->method('get')
            ->with(ConfigurationProvider::Default)
            ->willReturn($configurationProvider);

        $configurationProvider
            ->expects($this->once())
            ->method('all')
            ->willReturn($expectedSettings);

        $handler = new RetrieveSettingsHandler($configurationLocator);
        $result = $handler->__invoke($query);

        $this->assertSame($expectedSettings, $result);
    }

    #[Test]
    public function itShouldValidateProviderEnumConversion(): void {
        $query = new RetrieveSettingsQuery('default');

        $configurationProvider = $this->createMock(ConfigurationProviderInterface::class);
        $configurationLocator = $this->createMock(ConfigurationProviderLocator::class);

        $configurationLocator
            ->expects($this->once())
            ->method('get')
            ->with(ConfigurationProvider::Default)
            ->willReturn($configurationProvider);

        $configurationProvider
            ->expects($this->once())
            ->method('all')
            ->willReturn([]);

        $handler = new RetrieveSettingsHandler($configurationLocator);
        $result = $handler->__invoke($query);

        $this->assertSame([], $result);
    }
}
