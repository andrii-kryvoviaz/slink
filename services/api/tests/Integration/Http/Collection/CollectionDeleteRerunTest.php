<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Collection;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Test;
use Slink\Shared\Domain\FileSystem\Storage\StorageInterface;
use Tests\Integration\Http\HttpTestCase;

final class CollectionDeleteRerunTest extends HttpTestCase {
  private function connection(): Connection {
    /** @var EntityManagerInterface $entityManager */
    $entityManager = static::getContainer()->get(EntityManagerInterface::class);

    return $entityManager->getConnection();
  }

  private function storage(): StorageInterface {
    /** @var StorageInterface $storage */
    $storage = static::getContainer()->get(StorageInterface::class);

    return $storage;
  }

  private function deleteCollectionWithImages(string $token, string $collectionId): int {
    return $this->apiRequest(
      'DELETE',
      '/api/collection',
      $token,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['id' => $collectionId, 'deleteImages' => true], JSON_THROW_ON_ERROR),
    );
  }

  private function countWhere(string $table, string $column, string $value): int {
    return (int) $this->connection()->fetchOne(
      \sprintf('SELECT COUNT(*) FROM "%s" WHERE %s = ?', $table, $column),
      [$value],
    );
  }

  #[Test]
  public function aFailedImageDeleteKeepsItsCollectionItemSoARerunFinishesTheJob(): void {
    $this->createUser('collection-rerun-owner@local.test', 'collectionrerunowner', self::PASSWORD);
    $token = $this->login('collectionrerunowner', self::PASSWORD);
    $collectionId = $this->createCollection($token);

    $imageIds = [];
    foreach (\range(1, 3) as $ignored) {
      $imageId = $this->uploadImage($token, false);
      $this->addImageToCollection($token, $collectionId, $imageId);
      $imageIds[] = $imageId;
    }

    $stuckId = $imageIds[1];
    $connection = $this->connection();
    $fileNames = [];
    foreach ($imageIds as $imageId) {
      $fileNames[$imageId] = (string) $connection->fetchOne('SELECT file_name FROM image WHERE uuid = ?', [$imageId]);
      self::assertNotNull($this->storage()->readImage($fileNames[$imageId]), 'Precondition: the image file exists.');
    }

    $connection->executeStatement(
      'CREATE TRIGGER fail_stuck_image_delete BEFORE DELETE ON image WHEN OLD.uuid = '
      . $connection->quote($stuckId)
      . " BEGIN SELECT RAISE(ABORT, 'injected image delete failure'); END",
    );

    try {
      self::assertSame(500, $this->deleteCollectionWithImages($token, $collectionId));
      self::assertSame(
        1,
        $this->countWhere('collection_item', 'item_id', $stuckId),
        'The item of the image that failed to delete must stay in its collection.',
      );
    } finally {
      $connection->executeStatement('DROP TRIGGER fail_stuck_image_delete');
    }

    self::assertSame(
      204,
      $this->deleteCollectionWithImages($token, $collectionId),
      'Rerun failed: ' . (string) $this->client->getResponse()->getContent(),
    );

    self::assertSame(0, $this->countWhere('collection', 'uuid', $collectionId));
    self::assertSame(0, $this->countWhere('collection_item', 'collection_id', $collectionId));

    foreach ($imageIds as $imageId) {
      self::assertSame(0, $this->countWhere('image', 'uuid', $imageId), 'Image row must be gone.');
      self::assertNull($this->storage()->readImage($fileNames[$imageId]), 'Image file must be gone.');
    }
  }
}
