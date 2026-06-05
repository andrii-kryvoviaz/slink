<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Settings;

use EventSauce\EventSourcing\Snapshotting\SnapshotRepository;
use PHPUnit\Framework\Attributes\Test;
use Slink\Settings\Domain\Enum\ConfigurationProvider;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Settings\Domain\Repository\SettingStoreRepositoryInterface;
use Slink\Settings\Domain\Settings;
use Slink\Settings\Infrastructure\Provider\ConfigurationProviderLocator;
use Slink\Shared\Domain\ValueObject\ID;
use Tests\Integration\Http\HttpTestCase;

final class SettingsSnapshotTest extends HttpTestCase {
  private const int CHANGES_CROSSING_SNAPSHOT_THRESHOLD = 55;

  /**
   * @var list<string>
   */
  private const array UNTOUCHED_CATEGORY_KEYS = [
    'storage.provider',
    'user.allowRegistration',
    'user.password.minLength',
    'image.maxSize',
    'share.enableUrlShortening',
  ];

  #[Test]
  public function itPersistsSnapshotWhenCrossingThresholdAndResolvesUntouchedCategoriesFromConfiguredDefaults(): void {
    $lastWrittenValue = false;

    for ($i = 0; $i < self::CHANGES_CROSSING_SNAPSHOT_THRESHOLD; $i++) {
      $lastWrittenValue = $i % 2 === 0;

      $this->saveSettings('access', [
        'allowGuestUploads' => $lastWrittenValue,
        'allowUnauthenticatedAccess' => false,
        'requireSsl' => false,
        'requireAuthForMediaShares' => false,
        'requireAuthForCollectionShares' => false,
      ]);
    }

    $snapshot = $this->snapshotRepository()->retrieve(ID::fromString(Settings::getIdReference()));
    $this->assertNotNull($snapshot, 'A snapshot must be persisted once the change count crosses the threshold.');

    $settings = $this->settingsStore()->get();
    $this->assertSame($lastWrittenValue, $settings->get('access.allowGuestUploads'));

    $resolved = $this->configurationProvider();
    $fallback = $this->fallbackProvider();

    foreach (self::UNTOUCHED_CATEGORY_KEYS as $key) {
      $expected = $fallback->get($key);

      $this->assertNotNull(
        $expected,
        \sprintf('Configured default for "%s" must exist in the fallback provider.', $key),
      );

      $this->assertSame(
        $expected,
        $resolved->get($key),
        \sprintf('SettingsService must resolve untouched "%s" to the configured default.', $key),
      );
    }
  }

  private function snapshotRepository(): SnapshotRepository {
    /** @var SnapshotRepository $repository */
    $repository = static::getContainer()->get(SnapshotRepository::class);

    return $repository;
  }

  private function settingsStore(): SettingStoreRepositoryInterface {
    /** @var SettingStoreRepositoryInterface $store */
    $store = static::getContainer()->get(SettingStoreRepositoryInterface::class);

    return $store;
  }

  private function configurationProvider(): ConfigurationProviderInterface {
    /** @var ConfigurationProviderInterface $provider */
    $provider = static::getContainer()->get(ConfigurationProviderInterface::class);

    return $provider;
  }

  private function fallbackProvider(): ConfigurationProviderInterface {
    /** @var ConfigurationProviderLocator $locator */
    $locator = static::getContainer()->get(ConfigurationProviderLocator::class);

    return $locator->get(ConfigurationProvider::Default);
  }
}
