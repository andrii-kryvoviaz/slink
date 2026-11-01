<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Image;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\Integration\Http\HttpTestCase;

final class BatchReassignFlushFailureTest extends HttpTestCase {
  private function connection(): Connection {
    /** @var EntityManagerInterface $entityManager */
    $entityManager = static::getContainer()->get(EntityManagerInterface::class);

    return $entityManager->getConnection();
  }

  private function createTag(string $token, string $name): string {
    $status = $this->apiRequest('POST', '/api/tags', $token, ['CONTENT_TYPE' => 'application/json'], \json_encode(['name' => $name], JSON_THROW_ON_ERROR));

    self::assertSame(201, $status, (string) $this->client->getResponse()->getContent());

    return $this->extractId((string) $this->client->getResponse()->getContent());
  }

  /**
   * @return list<string>
   */
  private function itemIdsOf(string $collectionId): array {
    /** @var list<string> $itemIds */
    $itemIds = $this->connection()->fetchFirstColumn(
      'SELECT item_id FROM collection_item WHERE collection_id = ? ORDER BY position',
      [$collectionId],
    );

    return $itemIds;
  }

  /**
   * @return list<string>
   */
  private function tagIdsOf(string $imageId): array {
    /** @var list<string> $tagIds */
    $tagIds = $this->connection()->fetchFirstColumn('SELECT tag_id FROM image_to_tag WHERE image_id = ?', [$imageId]);

    return $tagIds;
  }

  #[Test]
  public function aFailedFlushOnOneImageFailsOnlyThatImageAndTheRestOfTheBatchStillCommits(): void {
    $this->createUser('batch-flush-owner@local.test', 'batchflushowner', self::PASSWORD);
    $token = $this->login('batchflushowner', self::PASSWORD);
    $collectionId = $this->createCollection($token);
    $tagId = $this->createTag($token, 'after-failure');
    $firstId = $this->uploadImage($token, false);
    $brokenId = $this->uploadImage($token, false);
    $lastId = $this->uploadImage($token, false);
    $connection = $this->connection();
    $connection->executeStatement(
      'CREATE TRIGGER fail_broken_item_insert BEFORE INSERT ON collection_item WHEN NEW.item_id = '
      . $connection->quote($brokenId)
      . " BEGIN SELECT RAISE(ABORT, 'injected item insert failure'); END",
    );

    try {
      $status = $this->apiRequest('PUT', '/api/images/batch', $token, ['CONTENT_TYPE' => 'application/json'], \json_encode([
        'assignments' => [
          $firstId => ['collectionIds' => [$collectionId]],
          $brokenId => ['collectionIds' => [$collectionId]],
          $lastId => ['collectionIds' => [$collectionId], 'tagIds' => [$tagId]],
        ],
      ], JSON_THROW_ON_ERROR));
    } finally {
      $connection->executeStatement('DROP TRIGGER fail_broken_item_insert');
    }

    self::assertSame(200, $status, (string) $this->client->getResponse()->getContent());

    /** @var array{processed: list<string>, failed: list<array{id: string, reason: string}>} $result */
    $result = $this->responsePayload()['data'] ?? $this->responsePayload();
    self::assertSame([$firstId, $lastId], $result['processed']);
    self::assertSame([$brokenId], \array_column($result['failed'], 'id'));
    self::assertStringNotContainsString('closed', $result['failed'][0]['reason']);
    self::assertSame([$firstId, $lastId], $this->itemIdsOf($collectionId));
    self::assertSame([$tagId], $this->tagIdsOf($lastId));
  }
}
