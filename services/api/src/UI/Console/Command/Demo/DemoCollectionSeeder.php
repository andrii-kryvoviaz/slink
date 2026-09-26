<?php

declare(strict_types=1);

namespace UI\Console\Command\Demo;

use Slink\Collection\Application\Command\AddItemToCollection\AddItemToCollectionCommand;
use Slink\Collection\Application\Command\CreateCollection\CreateCollectionCommand;
use Slink\Collection\Domain\Enum\ItemType;
use Slink\Collection\Domain\Repository\CollectionItemRepositoryInterface;
use Slink\Collection\Domain\Repository\CollectionRepositoryInterface;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Shared\Application\Command\CommandTrait;
use Slink\Shared\Domain\ValueObject\ID;

final class DemoCollectionSeeder {
  use CommandTrait;

  private const array COLLECTIONS = [
    'High Places' => [
      'Peaks, ridgelines and the valleys below them.',
      [
        'daniel-roe-lpjb_UMOyx8-unsplash.jpg',
        'jeremy-bishop-dR_q93lfaTw-unsplash.jpg',
        'jordan-steranka-lpddCskeg4A-unsplash.jpg',
        'luca-bravo-WeFDiEDModQ-unsplash.jpg',
        'piotr-pekala-t4cbHkeyXz4-unsplash.jpg',
        'samuel-ferrara-dKJXkKCF2D8-unsplash.jpg',
        'john-baker-anwxFFJ7tHw-unsplash.jpg',
        'kazuend-2KXEb_8G5vo-unsplash.jpg',
      ],
    ],
    'In Bloom' => [
      'Petals, blossoms and garden colour up close.',
      [
        'elijah-pilchard-D4WXuiwF5yk-unsplash.jpg',
        'george-pisarevsky-4lM10E18HdQ-unsplash.jpg',
        'george-pisarevsky-EsxDnpNYfPA-unsplash.jpg',
        'jei-lee-yRXuXvy4sQ4-unsplash.jpg',
        'kamil-klyta-CT8hJcAT9uA-unsplash.jpg',
        'nicolette-meade-RL3F99l0XYE-unsplash.jpg',
        'saffu-A7RzCegedb4-unsplash.jpg',
      ],
    ],
    'Forest Quiet' => [
      'Fog, pines and deep green shade.',
      [
        'christian-grab-AygJgCM_vto-unsplash.jpg',
        'filip-zrnzevic-_EMkxLdko9k-unsplash.jpg',
        'marcus-dietachmair-MjrjF0D1k-E-unsplash.jpg',
        'nahil-naseer-xljtGZ2-P3Y-unsplash.jpg',
        'joyce-g-C_Hn9I2AU8I-unsplash.jpg',
        'jeremy-bishop-dvACrXUExLs-unsplash.jpg',
      ],
    ],
    'After Dark' => [
      'City lights, sunsets and starry skies.',
      [
        'aaron-lau-EOnlL3L3IgQ-unsplash.jpg',
        'clay-banks-Icfk7F4Ku0w-unsplash.jpg',
        'malte-schmidt-enGr5YbjQKQ-unsplash.jpg',
        'kazuend-2KXEb_8G5vo-unsplash.jpg',
        'nate-rayfield-_WR6tUIAJe8-unsplash.jpg',
        'jordan-steranka-lpddCskeg4A-unsplash.jpg',
      ],
    ],
  ];

  public function __construct(
    private readonly CollectionRepositoryInterface $collectionRepository,
    private readonly CollectionItemRepositoryInterface $collectionItemRepository,
  ) {
  }

  /**
   * @param array<string, ImageView> $images
   * @return array<string, string>
   */
  public function seed(ID $userId, array $images, DemoSeedReport $report): array {
    $existingIds = $this->existingCollectionIds($userId);
    $collectionIds = [];

    foreach (self::COLLECTIONS as $name => [$description, $fileNames]) {
      $collectionId = $report->attempt(
        $name,
        fn() => $this->ensureCollection($userId, $name, $description, $existingIds[$name] ?? null, $report),
      );

      if ($collectionId === null) {
        continue;
      }

      $collectionIds[$name] = $collectionId;
      $this->addMissingItems($userId, $collectionId, $name, $fileNames, $images, $report);
    }

    return $collectionIds;
  }

  /**
   * @return array<string, string>
   */
  private function existingCollectionIds(ID $userId): array {
    $collectionIds = [];
    $ids = array_values($this->collectionRepository->findIdsByUserId($userId->toString()));

    foreach ($this->collectionRepository->findByIds($ids) as $collection) {
      $collectionIds[$collection->getName()] = $collection->getUuid();
    }

    return $collectionIds;
  }

  private function ensureCollection(ID $userId, string $name, string $description, ?string $existingId, DemoSeedReport $report): string {
    if ($existingId !== null) {
      $report->skipped($name);

      return $existingId;
    }

    $command = new CreateCollectionCommand($name, $description);
    $this->handle($command->withContext(['userId' => $userId->toString()]));
    $report->created($name);

    return $command->getId()->toString();
  }

  /**
   * @param list<string> $fileNames
   * @param array<string, ImageView> $images
   */
  private function addMissingItems(ID $userId, string $collectionId, string $name, array $fileNames, array $images, DemoSeedReport $report): void {
    foreach ($fileNames as $fileName) {
      $image = $images[$fileName] ?? null;

      if ($image === null) {
        continue;
      }

      $label = sprintf('%s %s', $name, $fileName);
      $report->attempt($label, fn() => $this->addItem($userId, $collectionId, $image->getUuid(), $label, $report));
    }
  }

  private function addItem(ID $userId, string $collectionId, string $imageId, string $label, DemoSeedReport $report): void {
    if ($this->collectionItemRepository->findByCollectionAndItemId($collectionId, $imageId) !== null) {
      $report->skipped($label);

      return;
    }

    $command = new AddItemToCollectionCommand($collectionId, $imageId, ItemType::Image);
    $this->handle($command->withContext(['userId' => $userId->toString()]));
    $report->created($label);
  }
}
