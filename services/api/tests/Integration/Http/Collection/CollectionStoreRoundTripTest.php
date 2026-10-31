<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Collection;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Slink\Collection\Domain\Collection;
use Slink\Collection\Domain\Repository\CollectionStoreRepositoryInterface;
use Slink\Collection\Domain\ValueObject\CollectionDescription;
use Slink\Collection\Domain\ValueObject\CollectionItem;
use Slink\Collection\Domain\ValueObject\CollectionName;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\User\Domain\Repository\UserStoreRepositoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Tests\Integration\Http\Store\ViewBackedStoreRoundTripTestCase;

/**
 * @extends ViewBackedStoreRoundTripTestCase<Collection>
 */
#[Group('sc-1178')]
final class CollectionStoreRoundTripTest extends ViewBackedStoreRoundTripTestCase {
  private const string TIMESTAMP_FORMAT = 'Y-m-d H:i:s';
  private const string UNKNOWN_COLLECTION_ID = '11111111-2222-4333-8444-555555555555';

  private string $ownerId = '';
  private string $ownerToken = '';

  #[\Override]
  protected function createAggregate(): ID {
    $this->bootOwner();
    $collectionId = $this->createDescribedCollection('Round trip', 'Reloads the same');
    [$first, $second, $third] = $this->uploadImages(3);

    foreach ([$first, $second, $third] as $imageId) {
      $this->addItem($collectionId, $imageId);
    }

    $this->reorderSucceeds($collectionId, [$third, $first, $second]);
    self::assertSame(204, $this->removeItem($collectionId, $first));

    return ID::fromString($collectionId);
  }

  #[\Override]
  protected function fetch(ID $id): Collection {
    return $this->collectionStore()->get($id);
  }

  #[\Override]
  protected function aggregateState(object $aggregate): array {
    return [
      'name' => $aggregate->getName()->toString(),
      'description' => $aggregate->getDescription()->toString(),
      'ownerId' => $aggregate->getUserId()->toString(),
      'createdAt' => $aggregate->getCreatedAt()->format(self::TIMESTAMP_FORMAT),
      'updatedAt' => $aggregate->getUpdatedAt()?->format(self::TIMESTAMP_FORMAT),
      'items' => \array_map(
        fn (CollectionItem $item): array => [
          'itemId' => $item->getItemId()->toString(),
          'itemType' => $item->getItemType()->value,
          'position' => $item->getPosition(),
          'addedAt' => $item->getAddedAt()->format(self::TIMESTAMP_FORMAT),
        ],
        \array_values($aggregate->getItems()->getSortedItems()),
      ),
    ];
  }

  #[\Override]
  protected function storedState(ID $id): array {
    $row = $this->readModel()->fetchAssociative('SELECT * FROM "collection" WHERE uuid = ?', [$id->toString()]);
    self::assertIsArray($row);

    return [
      'name' => $row['name'],
      'description' => $row['description'],
      'ownerId' => $row['user_id'],
      'createdAt' => $this->secondsOf($row['created_at']),
      'updatedAt' => $this->secondsOf($row['updated_at']),
      'items' => \array_map(
        fn (array $item): array => [
          'itemId' => $item['item_id'],
          'itemType' => $item['item_type'],
          'position' => (float) $item['position'],
          'addedAt' => $this->secondsOf($item['added_at']),
        ],
        $this->itemRows($id->toString()),
      ),
    ];
  }

  #[\Override]
  protected function rootTable(): string {
    return 'collection';
  }

  #[Test]
  public function everyCollectionCommandUpdatesTheRows(): void {
    $this->bootOwner();
    [$first, $second] = $this->uploadImages(2);

    $collectionId = $this->createDescribedCollection('Commands', 'Before the rename');
    $this->addItem($collectionId, $first);
    $this->addItem($collectionId, $second);
    $this->reorderSucceeds($collectionId, [$second, $first]);
    self::assertSame([$second, $first], $this->storedItemIds($collectionId));
    self::assertContains($this->updateCollection($collectionId, 'Renamed', 'After the rename'), [200, 204]);
    self::assertSame(204, $this->removeItem($collectionId, $first));
    self::assertSame([$second], $this->storedItemIds($collectionId));
    self::assertSame(204, $this->deleteCollection($collectionId));
    self::assertSame(0, $this->countRowsByColumn('collection', 'uuid', $collectionId));
  }

  #[Test]
  public function reloadedCollectionHoldsTheStoredFieldsAndItems(): void {
    $this->bootOwner();
    $collectionId = $this->createDescribedCollection('Before', 'Before');
    [$imageId] = $this->uploadImages(1);
    $this->addItem($collectionId, $imageId);
    $this->readModel()->executeStatement(
      'UPDATE "collection" SET name = ?, description = ?, created_at = ?, updated_at = ? WHERE uuid = ?',
      ['Stored name', 'Stored description', '2021-02-03 04:05:06', '2022-03-04 05:06:07', $collectionId],
    );
    $this->readModel()->executeStatement(
      'UPDATE "collection_item" SET position = ?, added_at = ? WHERE collection_id = ?',
      [7.5, '2023-04-05 06:07:08', $collectionId],
    );

    $collection = $this->reload(ID::fromString($collectionId));

    self::assertSame('Stored name', $collection->getName()->toString());
    self::assertSame('Stored description', $collection->getDescription()->toString());
    self::assertSame($this->ownerId, $collection->getUserId()->toString());
    self::assertSame('2021-02-03 04:05:06', $collection->getCreatedAt()->format(self::TIMESTAMP_FORMAT));
    self::assertSame('2022-03-04 05:06:07', $collection->getUpdatedAt()?->format(self::TIMESTAMP_FORMAT));
    self::assertSame(1, $collection->getItemCount());
    $item = $collection->getItems()->get(ID::fromString($imageId));
    self::assertInstanceOf(CollectionItem::class, $item);
    self::assertSame('image', $item->getItemType()->value);
    self::assertSame(7.5, $item->getPosition());
    self::assertSame('2023-04-05 06:07:08', $item->getAddedAt()->format(self::TIMESTAMP_FORMAT));
  }

  #[Test]
  public function aCollectionWithoutDescriptionItemsOrUpdatesReloadsEmpty(): void {
    $this->bootOwner();
    $collectionId = $this->createCollection($this->ownerToken);
    $this->readModel()->executeStatement('UPDATE "collection" SET updated_at = NULL WHERE uuid = ?', [$collectionId]);

    $collection = $this->reload(ID::fromString($collectionId));

    self::assertSame('functional-collection', $collection->getName()->toString());
    self::assertSame('', $collection->getDescription()->toString());
    self::assertNull($collection->getUpdatedAt());
    self::assertSame(0, $collection->getItemCount());
  }

  #[Test]
  public function reloadingAfterAnImageIsDeletedLeavesItsItemOut(): void {
    $this->bootOwner();
    [$collectionId, $first, $second, $third] = $this->collectionOfThreeImages();

    $this->deleteImage($second);

    self::assertSame([$first, $third], $this->reloadedItemIds($collectionId));
  }

  #[Test]
  public function reorderingTheRemainingItemsSucceedsAfterAnImageIsDeleted(): void {
    $this->bootOwner();
    [$collectionId, $first, $second, $third] = $this->collectionOfThreeImages();
    $this->deleteImage($second);

    $this->reorderSucceeds($collectionId, [$third, $first]);

    self::assertSame([$third, $first], $this->storedItemIds($collectionId));
    self::assertSame([$third, $first], $this->reloadedItemIds($collectionId));
  }

  #[Test]
  public function removingARemainingItemSucceedsAfterAnImageIsDeleted(): void {
    $this->bootOwner();
    [$collectionId, $first, $second, $third] = $this->collectionOfThreeImages();
    $this->deleteImage($second);

    self::assertSame(204, $this->removeItem($collectionId, $first));

    self::assertSame([$third], $this->storedItemIds($collectionId));
    self::assertSame([$third], $this->reloadedItemIds($collectionId));
  }

  #[Test]
  public function anItemAddedAfterAnImageIsDeletedFollowsTheRemainingItems(): void {
    $this->bootOwner();
    [$collectionId, $first, $second, $third] = $this->collectionOfThreeImages();
    $this->deleteImage($third);
    [$fourth] = $this->uploadImages(1);

    $this->addItem($collectionId, $fourth);

    self::assertSame([$first, $second, $fourth], $this->storedItemIds($collectionId));
    self::assertSame(
      (float) $this->itemRows($collectionId)[1]['position'] + 1.0,
      (float) $this->itemRows($collectionId)[2]['position'],
    );
  }

  #[Test]
  public function deletingACollectionWithItsImagesPassesOverAnImageAlreadyDeleted(): void {
    $this->bootOwner();
    [$collectionId, $first, $second, $third] = $this->collectionOfThreeImages();
    $this->deleteImage($second);

    self::assertSame(204, $this->deleteCollection($collectionId, true));

    self::assertSame(0, $this->countRowsByColumn('collection', 'uuid', $collectionId));
    self::assertSame(0, $this->countRowsByColumn('image', 'uuid', $first));
    self::assertSame(0, $this->countRowsByColumn('image', 'uuid', $third));
  }

  #[Test]
  public function imagesReassignedIntoACollectionInOneBatchKeepTheirOwnPositions(): void {
    $this->bootOwner();
    $collectionId = $this->createCollection($this->ownerToken);
    [$first, $second] = $this->uploadImages(2);

    $status = $this->apiRequest(
      'PUT',
      '/api/images/batch',
      $this->ownerToken,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode([
        'assignments' => [
          $first => ['collectionIds' => [$collectionId]],
          $second => ['collectionIds' => [$collectionId]],
        ],
      ], JSON_THROW_ON_ERROR),
    );

    self::assertSame(200, $status, (string) $this->client->getResponse()->getContent());
    self::assertSame([$first, $second], $this->storedItemIds($collectionId));
    self::assertSame([1.0, 2.0], \array_map(
      static fn (array $item): float => (float) $item['position'],
      $this->itemRows($collectionId),
    ));
  }

  #[Test]
  public function deletingACollectionThatDoesNotExistAnswersNotFound(): void {
    $this->bootOwner();

    self::assertSame(404, $this->deleteCollection(self::UNKNOWN_COLLECTION_ID));
  }

  #[Test]
  public function deletingADeletedCollectionAnswersNotFound(): void {
    $this->bootOwner();
    $collectionId = $this->createCollection($this->ownerToken);

    self::assertSame(204, $this->deleteCollection($collectionId));
    self::assertSame(404, $this->deleteCollection($collectionId));
  }

  #[Test]
  public function loadingADeletedCollectionThroughTheStoreThrowsNotFound(): void {
    $this->bootOwner();
    $collectionId = $this->createCollection($this->ownerToken);
    self::assertSame(204, $this->deleteCollection($collectionId));

    $this->expectException(NotFoundException::class);

    $this->reload(ID::fromString($collectionId));
  }

  #[Test]
  public function renamingADeletedCollectionAnswersNotFound(): void {
    $this->bootOwner();
    [$collectionId] = $this->deletedCollectionWithAnImage();

    self::assertSame(404, $this->updateCollection($collectionId, 'Renamed', ''));
  }

  #[Test]
  public function reorderingADeletedCollectionAnswersNotFound(): void {
    $this->bootOwner();
    [$collectionId, $imageId] = $this->deletedCollectionWithAnImage();

    self::assertSame(404, $this->reorder($collectionId, [$imageId]));
  }

  #[Test]
  public function addingToADeletedCollectionAnswersNotFound(): void {
    $this->bootOwner();
    [$collectionId, $imageId] = $this->deletedCollectionWithAnImage();

    self::assertSame(404, $this->apiRequest(
      'POST',
      \sprintf('/api/collection/%s/items/%s', $collectionId, $imageId),
      $this->ownerToken,
    ));
  }

  #[Test]
  public function removingFromADeletedCollectionAnswersNotFound(): void {
    $this->bootOwner();
    [$collectionId, $imageId] = $this->deletedCollectionWithAnImage();

    self::assertSame(404, $this->removeItem($collectionId, $imageId));
  }

  #[Test]
  public function uploadingIntoADeletedCollectionAnswersNotFoundAndKeepsNoImage(): void {
    $this->bootOwner();
    $collectionId = $this->createCollection($this->ownerToken);
    self::assertSame(204, $this->deleteCollection($collectionId));

    self::assertSame(404, $this->uploadImageWithFields($this->ownerToken, ['collectionIds' => [$collectionId]]));

    self::assertSame(0, (int) $this->readModel()->fetchOne('SELECT COUNT(*) FROM "image"'));
  }

  #[Test]
  public function purgingTheOwnerDeletesTheirCollection(): void {
    $adminToken = $this->bootAdmin();
    $this->bootOwner();
    [$collectionId] = $this->collectionOfThreeImages();

    self::assertSame(204, $this->apiRequest('DELETE', '/api/user/' . $this->ownerId, $adminToken));

    self::assertSame(0, $this->countRowsByColumn('collection', 'uuid', $collectionId));
    self::assertSame(0, $this->countRowsByColumn('collection_item', 'collection_id', $collectionId));
    $this->expectException(NotFoundException::class);
    $this->reload(ID::fromString($collectionId));
  }

  #[Test]
  public function loadingManyReturnsTheCollectionsFoundAndLeavesTheMissingOnesOut(): void {
    $this->bootOwner();
    $collectionId = $this->createCollection($this->ownerToken);
    $this->entityManager()->clear();

    $collections = $this->collectionStore()->getByIds([$collectionId, self::UNKNOWN_COLLECTION_ID]);

    self::assertTrue($collections->has($collectionId));
    self::assertFalse($collections->has(self::UNKNOWN_COLLECTION_ID));
  }

  #[Test]
  public function theSecondOfTwoConcurrentWritersFailsAndTheFirstWritersValuesStay(): void {
    $collectionId = $this->createAggregate();
    $firstContainer = $this->independentContainer();
    $secondContainer = $this->independentContainer();
    $firstLoad = $this->collectionStoreOf($firstContainer)->get($collectionId);
    $secondLoad = $this->collectionStoreOf($secondContainer)->get($collectionId);

    $this->renameAndStore($firstContainer, $firstLoad, 'First writer');

    self::assertCausedBy(OptimisticLockException::class, $this->failureOf(
      fn () => $this->renameAndStore($secondContainer, $secondLoad, 'Second writer'),
    ));
    self::assertSame('First writer', $this->storedState($collectionId)['name']);
    self::assertSame('First writer', $this->reload($collectionId)->getName()->toString());
  }

  #[Test]
  public function viewBackedCollectionsAndUsersWorkSideBySide(): void {
    $this->bootOwner();

    $collectionId = $this->createCollection($this->ownerToken);

    self::assertSame('functional-collection', $this->reload(ID::fromString($collectionId))->getName()->toString());

    /** @var UserStoreRepositoryInterface $users */
    $users = static::getContainer()->get(UserStoreRepositoryInterface::class);
    self::assertSame($this->ownerId, $users->get(ID::fromString($this->ownerId))->aggregateRootId()->toString());
  }

  private function bootOwner(): void {
    $this->ownerId = $this->createUser('collection-store-owner@local.test', 'collectionstoreowner', self::PASSWORD);
    $this->ownerToken = $this->login('collectionstoreowner', self::PASSWORD);
  }

  private function createDescribedCollection(string $name, string $description): string {
    $this->entityManager()->clear();
    $status = $this->apiRequest(
      'POST',
      '/api/collection',
      $this->ownerToken,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['name' => $name, 'description' => $description], JSON_THROW_ON_ERROR),
    );

    self::assertContains($status, [200, 201], (string) $this->client->getResponse()->getContent());

    return $this->extractId((string) $this->client->getResponse()->getContent());
  }

  /**
   * @return list<string>
   */
  private function uploadImages(int $count): array {
    $imageIds = [];

    for ($uploaded = 0; $uploaded < $count; $uploaded++) {
      $imageIds[] = $this->uploadImage($this->ownerToken, false);
    }

    return $imageIds;
  }

  /**
   * @return array{0: string, 1: string, 2: string, 3: string}
   */
  private function collectionOfThreeImages(): array {
    $collectionId = $this->createCollection($this->ownerToken);
    [$first, $second, $third] = $this->uploadImages(3);

    foreach ([$first, $second, $third] as $imageId) {
      $this->addItem($collectionId, $imageId);
    }

    return [$collectionId, $first, $second, $third];
  }

  /**
   * @return array{0: string, 1: string}
   */
  private function deletedCollectionWithAnImage(): array {
    $collectionId = $this->createCollection($this->ownerToken);
    [$imageId] = $this->uploadImages(1);
    $this->addItem($collectionId, $imageId);
    self::assertSame(204, $this->deleteCollection($collectionId));

    return [$collectionId, $imageId];
  }

  private function addItem(string $collectionId, string $imageId): void {
    $this->entityManager()->clear();
    $this->addImageToCollection($this->ownerToken, $collectionId, $imageId);

    self::assertLessThan(300, $this->client->getResponse()->getStatusCode(), (string) $this->client->getResponse()->getContent());
  }

  private function removeItem(string $collectionId, string $imageId): int {
    $this->entityManager()->clear();

    return $this->apiRequest(
      'DELETE',
      \sprintf('/api/collection/%s/items/%s', $collectionId, $imageId),
      $this->ownerToken,
    );
  }

  /**
   * @param list<string> $orderedItemIds
   */
  private function reorder(string $collectionId, array $orderedItemIds): int {
    $this->entityManager()->clear();

    return $this->apiRequest(
      'PATCH',
      '/api/collection/items/order',
      $this->ownerToken,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['collectionId' => $collectionId, 'orderedItemIds' => $orderedItemIds], JSON_THROW_ON_ERROR),
    );
  }

  /**
   * @param list<string> $orderedItemIds
   */
  private function reorderSucceeds(string $collectionId, array $orderedItemIds): void {
    self::assertContains(
      $this->reorder($collectionId, $orderedItemIds),
      [200, 204],
      (string) $this->client->getResponse()->getContent(),
    );
  }

  private function updateCollection(string $collectionId, string $name, string $description): int {
    $this->entityManager()->clear();

    return $this->apiRequest(
      'PATCH',
      '/api/collection',
      $this->ownerToken,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['id' => $collectionId, 'name' => $name, 'description' => $description], JSON_THROW_ON_ERROR),
    );
  }

  private function deleteCollection(string $collectionId, bool $deleteImages = false): int {
    $this->entityManager()->clear();

    return $this->apiRequest(
      'DELETE',
      '/api/collection',
      $this->ownerToken,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['id' => $collectionId, 'deleteImages' => $deleteImages], JSON_THROW_ON_ERROR),
    );
  }

  private function deleteImage(string $imageId): void {
    $this->entityManager()->clear();

    self::assertContains(
      $this->apiRequest('DELETE', \sprintf('/api/image/%s', $imageId), $this->ownerToken),
      [200, 204],
      (string) $this->client->getResponse()->getContent(),
    );
    self::assertSame(0, $this->countRowsByColumn('collection_item', 'item_id', $imageId));
  }

  private function renameAndStore(ContainerInterface $container, Collection $collection, string $name): void {
    $collection->update(CollectionName::fromString($name), CollectionDescription::fromString('Concurrent'));
    $this->collectionStoreOf($container)->store($collection);

    /** @var EntityManagerInterface $entityManager */
    $entityManager = $container->get(EntityManagerInterface::class);
    $entityManager->flush();
  }

  /**
   * @return list<array<string, mixed>>
   */
  private function itemRows(string $collectionId): array {
    return $this->readModel()->fetchAllAssociative(
      'SELECT * FROM "collection_item" WHERE collection_id = ? ORDER BY position ASC',
      [$collectionId],
    );
  }

  /**
   * @return list<mixed>
   */
  private function storedItemIds(string $collectionId): array {
    return \array_column($this->itemRows($collectionId), 'item_id');
  }

  /**
   * @return list<string>
   */
  private function reloadedItemIds(string $collectionId): array {
    return \array_map(
      static fn (CollectionItem $item): string => $item->getItemId()->toString(),
      \array_values($this->reload(ID::fromString($collectionId))->getItems()->getSortedItems()),
    );
  }

  private function collectionStore(): CollectionStoreRepositoryInterface {
    return $this->collectionStoreOf(static::getContainer());
  }

  private function collectionStoreOf(ContainerInterface $container): CollectionStoreRepositoryInterface {
    /** @var CollectionStoreRepositoryInterface $store */
    $store = $container->get(CollectionStoreRepositoryInterface::class);

    return $store;
  }
}
