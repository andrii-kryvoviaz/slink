<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Collection;

use PHPUnit\Framework\Attributes\Test;
use Tests\Integration\Http\HttpTestCase;

final class CollectionCrudTest extends HttpTestCase {
  private string $ownerToken = '';

  private function bootOwner(): void {
    $this->createUser('collection-crud-owner@local.test', 'collectioncrudowner', self::PASSWORD);
    $this->ownerToken = $this->login('collectioncrudowner', self::PASSWORD);
  }

  /**
   * @return array<int, array<string, mixed>>
   */
  private function listCollections(): array {
    $status = $this->apiRequest('GET', '/api/collections', $this->ownerToken);
    self::assertSame(200, $status, 'List collections failed: ' . (string) $this->client->getResponse()->getContent());

    /** @var array{data?: array<int, array<string, mixed>>} $payload */
    $payload = \json_decode((string) $this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

    return $payload['data'] ?? [];
  }

  /**
   * @return array<int, array<string, mixed>>
   */
  private function listCollectionItems(string $collectionId): array {
    $status = $this->apiRequest('GET', \sprintf('/api/collection/%s/items', $collectionId), $this->ownerToken);
    self::assertSame(200, $status, 'List collection items failed: ' . (string) $this->client->getResponse()->getContent());

    /** @var array{data?: array<int, array<string, mixed>>} $payload */
    $payload = \json_decode((string) $this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

    return $payload['data'] ?? [];
  }

  /**
   * @param array<int, array<string, mixed>> $collections
   */
  private function containsCollection(array $collections, string $collectionId): bool {
    foreach ($collections as $collection) {
      if (($collection['id'] ?? null) === $collectionId) {
        return true;
      }
    }

    return false;
  }

  /**
   * @param array<int, array<string, mixed>> $items
   */
  private function containsItem(array $items, string $imageId): bool {
    foreach ($items as $item) {
      if (($item['itemId'] ?? null) === $imageId) {
        return true;
      }
    }

    return false;
  }

  private function deleteCollection(string $collectionId): int {
    return $this->apiRequest(
      'DELETE',
      '/api/collection',
      $this->ownerToken,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['id' => $collectionId], JSON_THROW_ON_ERROR),
    );
  }

  #[Test]
  public function ownerCreatesCollectionAndItAppearsInList(): void {
    $this->bootOwner();

    $collectionId = $this->createCollection($this->ownerToken);
    self::assertNotSame('', $collectionId, 'Create collection did not return an id.');

    self::assertTrue(
      $this->containsCollection($this->listCollections(), $collectionId),
      'Created collection is missing from the user collection list.',
    );
  }

  #[Test]
  public function ownerAddsAndRemovesItemFromCollection(): void {
    $this->bootOwner();

    $collectionId = $this->createCollection($this->ownerToken);
    $imageId = $this->uploadImage($this->ownerToken, false);

    $this->addImageToCollection($this->ownerToken, $collectionId, $imageId);

    self::assertTrue(
      $this->containsItem($this->listCollectionItems($collectionId), $imageId),
      'Added image is missing from the collection items list.',
    );

    $removeStatus = $this->apiRequest(
      'DELETE',
      \sprintf('/api/collection/%s/items/%s', $collectionId, $imageId),
      $this->ownerToken,
    );
    self::assertSame(204, $removeStatus, 'Remove item failed: ' . (string) $this->client->getResponse()->getContent());

    self::assertFalse(
      $this->containsItem($this->listCollectionItems($collectionId), $imageId),
      'Removed image is still present in the collection items list.',
    );
  }

  #[Test]
  public function ownerDeletesCollectionAndItDisappearsFromList(): void {
    $this->bootOwner();

    $collectionId = $this->createCollection($this->ownerToken);
    self::assertTrue(
      $this->containsCollection($this->listCollections(), $collectionId),
      'Collection should be listed before deletion.',
    );

    self::assertSame(204, $this->deleteCollection($collectionId), 'Delete collection failed: ' . (string) $this->client->getResponse()->getContent());

    self::assertFalse(
      $this->containsCollection($this->listCollections(), $collectionId),
      'Deleted collection is still present in the user collection list.',
    );
  }
}
