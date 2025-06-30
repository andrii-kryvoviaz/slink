<?php

declare(strict_types=1);

namespace Unit\Slink\Settings\Application\Query\RetrieveSettings;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Slink\Settings\Application\Query\RetrieveSettings\RetrieveSettingsHandler;
use Slink\Settings\Application\Query\RetrieveSettings\RetrieveSettingsQuery;
use Slink\Settings\Domain\Enum\ConfigurationProvider;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Settings\Infrastructure\Provider\ConfigurationProviderLocator;

final class RetrieveSettingsHandlerTest extends TestCase {
    private MockObject $configurationLocator;
    private MockObject $configurationProvider;
    private RetrieveSettingsHandler $handler;

    protected function setUp(): void {
        $this->configurationLocator = $this->createMock(ConfigurationProviderLocator::class);
        $this->configurationProvider = $this->createMock(ConfigurationProviderInterface::class);
        $this->handler = new RetrieveSettingsHandler($this->configurationLocator);
    }

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

        $this->configurationLocator
            ->expects($this->once())
            ->method('get')
            ->with(ConfigurationProvider::Default)
            ->willReturn($this->configurationProvider);

        $this->configurationProvider
            ->expects($this->once())
            ->method('all')
            ->willReturn($expectedSettings);

        $result = $this->handler->__invoke($query);

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

        $this->configurationLocator
            ->expects($this->once())
            ->method('get')
            ->with(ConfigurationProvider::Store)
            ->willReturn($this->configurationProvider);

        $this->configurationProvider
            ->expects($this->once())
            ->method('all')
            ->willReturn($expectedSettings);

        $result = $this->handler->__invoke($query);

        $this->assertSame($expectedSettings, $result);
    }

    #[Test]
    public function itShouldHandleEmptySettings(): void {
        $query = new RetrieveSettingsQuery();

        $this->configurationLocator
            ->expects($this->once())
            ->method('get')
            ->with(ConfigurationProvider::Default)
            ->willReturn($this->configurationProvider);

        $this->configurationProvider
            ->expects($this->once())
            ->method('all')
            ->willReturn([]);

        $result = $this->handler->__invoke($query);

        $this->assertSame([], $result);
    }

    #[Test]
    public function itShouldThrowExceptionForInvalidProvider(): void {
        $query = new RetrieveSettingsQuery('invalid');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid configuration provider');

        $this->handler->__invoke($query);
    }

    #[Test]
    public function itShouldPropagateContainerException(): void {
        $query = new RetrieveSettingsQuery();

        $this->configurationLocator
            ->expects($this->once())
            ->method('get')
            ->willThrowException(new class extends \Exception implements ContainerExceptionInterface {});

        $this->expectException(ContainerExceptionInterface::class);

        $this->handler->__invoke($query);
    }

    #[Test]
    public function itShouldPropagateNotFoundException(): void {
        $query = new RetrieveSettingsQuery();

        $this->configurationLocator
            ->expects($this->once())
            ->method('get')
            ->willThrowException(new class extends \Exception implements NotFoundExceptionInterface {});

        $this->expectException(NotFoundExceptionInterface::class);

        $this->handler->__invoke($query);
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

        $this->configurationLocator
            ->expects($this->once())
            ->method('get')
            ->with(ConfigurationProvider::Default)
            ->willReturn($this->configurationProvider);

        $this->configurationProvider
            ->expects($this->once())
            ->method('all')
            ->willReturn($expectedSettings);

        $result = $this->handler->__invoke($query);

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

        $this->configurationLocator
            ->expects($this->once())
            ->method('get')
            ->with(ConfigurationProvider::Default)
            ->willReturn($this->configurationProvider);

        $this->configurationProvider
            ->expects($this->once())
            ->method('all')
            ->willReturn($expectedSettings);

        $result = $this->handler->__invoke($query);

        $this->assertSame($expectedSettings, $result);
    }

    #[Test]
    public function itShouldValidateProviderEnumConversion(): void {
        $query = new RetrieveSettingsQuery('default');

        $this->configurationLocator
            ->expects($this->once())
            ->method('get')
            ->with(ConfigurationProvider::Default)
            ->willReturn($this->configurationProvider);

        $this->configurationProvider
            ->expects($this->once())
            ->method('all')
            ->willReturn([]);

        $result = $this->handler->__invoke($query);

        $this->assertSame([], $result);
    }
}
