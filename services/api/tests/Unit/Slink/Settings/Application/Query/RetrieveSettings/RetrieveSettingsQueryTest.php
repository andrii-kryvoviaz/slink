<?php

declare(strict_types=1);

namespace Unit\Slink\Settings\Application\Query\RetrieveSettings;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Settings\Application\Query\RetrieveSettings\RetrieveSettingsQuery;
use Slink\Settings\Domain\Enum\ConfigurationProvider;

final class RetrieveSettingsQueryTest extends TestCase {
    #[Test]
    public function itShouldCreateWithDefaultProvider(): void {
        $query = new RetrieveSettingsQuery();
        
        $this->assertSame(ConfigurationProvider::Default->value, $query->getProvider());
    }

    #[Test]
    #[DataProvider('validProviderProvider')]
    public function itShouldCreateWithSpecificProvider(string $provider): void {
        $query = new RetrieveSettingsQuery($provider);
        
        $this->assertSame($provider, $query->getProvider());
    }

    #[Test]
    public function itShouldUseDefaultWhenNoProviderSpecified(): void {
        $query = new RetrieveSettingsQuery();
        
        $this->assertSame('default', $query->getProvider());
    }

    #[Test]
    public function itShouldAcceptStoreProvider(): void {
        $query = new RetrieveSettingsQuery('store');
        
        $this->assertSame('store', $query->getProvider());
    }

    #[Test]
    public function itShouldAcceptDefaultProviderExplicitly(): void {
        $query = new RetrieveSettingsQuery('default');
        
        $this->assertSame('default', $query->getProvider());
    }

    #[Test]
    public function itShouldPreserveProviderValue(): void {
        $provider = 'store';
        $query = new RetrieveSettingsQuery($provider);
        
        $this->assertSame($provider, $query->getProvider());
        
        $sameQuery = new RetrieveSettingsQuery($query->getProvider());
        $this->assertSame($provider, $sameQuery->getProvider());
    }

    /**
     * @return array<int, array<int, string>>
     */
    public static function validProviderProvider(): array {
        return [
            ['default'],
            ['store'],
        ];
    }
}
