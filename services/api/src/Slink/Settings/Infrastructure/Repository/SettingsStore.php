<?php

declare(strict_types=1);

namespace Slink\Settings\Infrastructure\Repository;

use EventSauce\EventSourcing\MessageDispatcher;
use Slink\Settings\Domain\Repository\SettingStoreRepositoryInterface;
use Slink\Settings\Domain\Settings;
use Slink\Settings\Infrastructure\Provider\DatabaseProvider;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractViewBackedStore;

/**
 * @extends AbstractViewBackedStore<Settings, \stdClass>
 */
final class SettingsStore extends AbstractViewBackedStore implements SettingStoreRepositoryInterface {
  public function __construct(
    MessageDispatcher $messageDispatcher,
    private readonly DatabaseProvider $databaseProvider,
  ) {
    parent::__construct($messageDispatcher);
  }

  protected static function getAggregateRootClass(): string {
    return Settings::class;
  }

  protected function findRootView(ID $id): object {
    return (object) $this->databaseProvider->all();
  }

  protected function hydrate(object $view): object {
    $rows = (array) $view;

    return Settings::fromView(
      $this->category($rows, 'storage'),
      $this->category($rows, 'user'),
      $this->category($rows, 'image'),
      $this->category($rows, 'access'),
      $this->category($rows, 'share'),
      $this->category($rows, 'customization'),
    );
  }

  public function get(): Settings {
    return $this->retrieve(ID::fromString(Settings::getIdReference()));
  }

  public function store(Settings $settings): void {
    $this->persist($settings);
  }

  /**
   * @param array<string, mixed> $rows
   * @return array<string, mixed>|null
   */
  private function category(array $rows, string $key): ?array {
    $category = $rows[$key] ?? null;

    if (!is_array($category)) {
      return null;
    }

    return $category;
  }
}
