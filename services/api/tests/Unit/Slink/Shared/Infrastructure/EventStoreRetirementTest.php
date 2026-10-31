<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Shared\Infrastructure;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

#[Group('sc-1183')]
final class EventStoreRetirementTest extends TestCase {
  private const string RETIRED_PACKAGE = 'eventsauce/message-repository-for-doctrine';

  /**
   * @return iterable<string, array{string}>
   */
  public static function retiredClasses(): iterable {
    yield 'serializer factory' => ['Slink\Shared\Infrastructure\Persistence\EventStore\UpcastingMessageSerializerFactory'];
    yield 'upcaster base' => ['Slink\Shared\Infrastructure\Persistence\EventStore\AbstractUpcaster'];
    yield 'settings upcaster' => ['Slink\Settings\Infrastructure\EventStore\Upcaster\SettingsChangedUpcaster'];
    yield 'user upcaster' => ['Slink\User\Infrastructure\EventStore\Upcaster\UserWasCreatedUpcaster'];
    yield 'api key upcaster' => ['Slink\User\Infrastructure\EventStore\Upcaster\ApiKeyWasCreatedUpcaster'];
  }

  /**
   * @return iterable<string, array{string}>
   */
  public static function retiredPaths(): iterable {
    yield 'migrations configuration' => ['config/migrations/event_store.yaml'];
    yield 'migrations directory' => ['src/Slink/Shared/Infrastructure/Persistence/EventStore/Migrations'];
  }

  #[Test]
  #[DataProvider('retiredClasses')]
  public function aRetiredClassNoLongerExists(string $class): void {
    self::assertFalse(\class_exists($class));
  }

  #[Test]
  #[DataProvider('retiredPaths')]
  public function aRetiredPathIsGone(string $path): void {
    self::assertFileDoesNotExist(self::projectDir() . '/' . $path);
  }

  #[Test]
  public function composerNoLongerRequiresOrLocksTheMessageRepository(): void {
    /** @var array{require: array<string, string>} $manifest */
    $manifest = \json_decode((string) \file_get_contents(self::projectDir() . '/composer.json'), true, 512, JSON_THROW_ON_ERROR);
    /** @var array{packages: list<array{name: string}>} $lock */
    $lock = \json_decode((string) \file_get_contents(self::projectDir() . '/composer.lock'), true, 512, JSON_THROW_ON_ERROR);
    $locked = \array_column($lock['packages'], 'name');

    self::assertArrayNotHasKey(self::RETIRED_PACKAGE, $manifest['require']);
    self::assertArrayHasKey('eventsauce/eventsauce', $manifest['require']);
    self::assertNotContains(self::RETIRED_PACKAGE, $locked);
    self::assertContains('eventsauce/eventsauce', $locked);
  }

  #[Test]
  public function noSourceOrConfigurationReferencesTheRetiredMachinery(): void {
    $references = [];

    foreach (['src', 'config'] as $directory) {
      $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(self::projectDir() . '/' . $directory, \FilesystemIterator::SKIP_DOTS));

      foreach ($files as $file) {
        if (\preg_match('/Upcast|DoctrineMessageRepository/', (string) \file_get_contents((string) $file)) === 1) {
          $references[] = (string) $file;
        }
      }
    }

    self::assertSame([], $references);
  }

  #[Test]
  public function theEventSauceConfigurationDeclaresNoMessageRepositoryOrSerializer(): void {
    /** @var array{parameters?: array<string, mixed>, services: array<string, mixed>} $configuration */
    $configuration = Yaml::parseFile(self::projectDir() . '/config/services/event_sauce.yaml');

    self::assertArrayNotHasKey('event_sauce.table_name', $configuration['parameters'] ?? []);
    self::assertArrayNotHasKey('EventSauce\EventSourcing\MessageRepository', $configuration['services']);
    self::assertArrayNotHasKey('event_sauce.message_serializer', $configuration['services']);
  }

  #[Test]
  public function theExampleEnvironmentNoLongerSetsTheDeprecatedVariable(): void {
    self::assertStringNotContainsString('ES_DATABASE_URL', (string) \file_get_contents(self::projectDir() . '/.env.example'));
  }

  private static function projectDir(): string {
    return \dirname(__DIR__, 5);
  }
}
