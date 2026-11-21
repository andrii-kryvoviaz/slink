<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Image;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\Integration\Http\HttpTestCase;

final class BatchReassignCollectionsTest extends HttpTestCase {
  private function itemCount(string $collectionId, string $imageId): int {
    /** @var EntityManagerInterface $entityManager */
    $entityManager = static::getContainer()->get(EntityManagerInterface::class);

    return (int) $entityManager->getConnection()->fetchOne(
      'SELECT COUNT(*) FROM collection_item WHERE collection_id = ? AND item_id = ?',
      [$collectionId, $imageId],
    );
  }

  #[Test]
  public function aRepeatedCollectionIdAddsTheImageOnce(): void {
    $this->createUser('batch-collections-owner@local.test', 'batchcollectionsowner', self::PASSWORD);
    $token = $this->login('batchcollectionsowner', self::PASSWORD);
    $imageId = $this->uploadImage($token, false);
    $collectionId = $this->createCollection($token);

    $status = $this->apiRequest('PUT', '/api/images/batch', $token, ['CONTENT_TYPE' => 'application/json'], \json_encode([
      'assignments' => [
        $imageId => ['collectionIds' => [$collectionId, $collectionId]],
      ],
    ], JSON_THROW_ON_ERROR));

    self::assertSame(200, $status, (string) $this->client->getResponse()->getContent());

    /** @var array{processed: list<string>, failed: list<array{id: string, reason: string}>} $result */
    $result = $this->responsePayload()['data'] ?? $this->responsePayload();
    self::assertSame([], $result['failed']);
    self::assertSame([$imageId], $result['processed']);
    self::assertSame(1, $this->itemCount($collectionId, $imageId));
  }
}
