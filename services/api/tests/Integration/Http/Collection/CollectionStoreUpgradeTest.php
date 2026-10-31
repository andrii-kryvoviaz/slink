<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Collection;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Slink\Collection\Domain\Collection;
use Slink\Collection\Domain\Repository\CollectionStoreRepositoryInterface;
use Slink\Collection\Domain\ValueObject\CollectionItem;
use Slink\Shared\Domain\ValueObject\ID;
use Tests\Integration\Console\Boot\RollsBackToPreviousRelease;
use Tests\Integration\Http\HttpTestCase;
use Tests\Integration\Http\Store\InspectsEventLog;

#[Group('sc-1178')]
final class CollectionStoreUpgradeTest extends HttpTestCase {
  use InspectsEventLog;
  use RollsBackToPreviousRelease;

  private string $ownerId = '';
  private string $ownerToken = '';
  private string $collectionId = '';
  private string $firstImageId = '';
  private string $secondImageId = '';

  protected function setUp(): void {
    parent::setUp();

    $this->ownerId = $this->createUser('collection-upgrade-owner@local.test', 'collectionupgradeowner', self::PASSWORD);
    $this->ownerToken = $this->login('collectionupgradeowner', self::PASSWORD);
    $this->collectionId = $this->createCollection($this->ownerToken);
    $this->firstImageId = $this->uploadImage($this->ownerToken, false);
    $this->secondImageId = $this->uploadImage($this->ownerToken, false);
    $this->addImageToCollection($this->ownerToken, $this->collectionId, $this->firstImageId);
    $this->addImageToCollection($this->ownerToken, $this->collectionId, $this->secondImageId);

    $this->rollBackToPreviousRelease();
    $this->bootApplicationOnPreviousReleaseDatabase();
  }

  protected function tearDown(): void {
    $this->restorePreviousReleaseBackup();

    parent::tearDown();
  }

  #[Test]
  public function aCollectionFromThePreviousReleaseReloadsItsRowAndItems(): void {
    $this->forgetEventStream();

    $collection = $this->reloaded();

    self::assertSame('functional-collection', $collection->getName()->toString());
    self::assertSame($this->ownerId, $collection->getUserId()->toString());
    self::assertSame([$this->firstImageId, $this->secondImageId], $this->itemIdsOf($collection));
  }

  #[Test]
  public function aCollectionFromThePreviousReleaseIsListedAndServesItsItems(): void {
    $this->forgetEventStream();

    self::assertSame(200, $this->apiRequest('GET', '/api/collections', $this->ownerToken));
    self::assertStringContainsString($this->collectionId, (string) $this->client->getResponse()->getContent());
    self::assertSame(
      200,
      $this->apiRequest('GET', \sprintf('/api/collection/%s/items', $this->collectionId), $this->ownerToken),
    );
    self::assertStringContainsString($this->firstImageId, (string) $this->client->getResponse()->getContent());
  }

  #[Test]
  public function aCollectionFromThePreviousReleaseRenames(): void {
    $this->forgetEventStream();

    self::assertContains($this->rename('renamed after upgrade'), [200, 204]);

    self::assertSame('renamed after upgrade', $this->reloaded()->getName()->toString());
  }

  #[Test]
  public function aCollectionFromThePreviousReleaseReorders(): void {
    $this->forgetEventStream();

    self::assertContains(
      $this->apiRequest(
        'PATCH',
        '/api/collection/items/order',
        $this->ownerToken,
        ['CONTENT_TYPE' => 'application/json'],
        \json_encode(
          ['collectionId' => $this->collectionId, 'orderedItemIds' => [$this->secondImageId, $this->firstImageId]],
          JSON_THROW_ON_ERROR,
        ),
      ),
      [200, 204],
    );

    self::assertSame([$this->secondImageId, $this->firstImageId], $this->itemIdsOf($this->reloaded()));
  }

  #[Test]
  public function aCollectionFromThePreviousReleaseTakesAndLosesItems(): void {
    $this->forgetEventStream();
    $thirdImageId = $this->uploadImage($this->ownerToken, false);

    $this->clearEntityManager();
    $this->addImageToCollection($this->ownerToken, $this->collectionId, $thirdImageId);
    $this->clearEntityManager();
    $removed = $this->apiRequest(
      'DELETE',
      \sprintf('/api/collection/%s/items/%s', $this->collectionId, $this->firstImageId),
      $this->ownerToken,
    );

    self::assertSame(204, $removed);
    self::assertSame([$this->secondImageId, $thirdImageId], $this->itemIdsOf($this->reloaded()));
  }

  #[Test]
  public function aCollectionFromThePreviousReleaseDeletesOnceThenAnswersNotFound(): void {
    $this->forgetEventStream();

    self::assertSame(204, $this->deleteCollection());
    self::assertSame(0, $this->countRowsByColumn('collection', 'uuid', $this->collectionId));
    self::assertSame(404, $this->deleteCollection());
  }

  #[Test]
  public function aCollectionStoredWithEscapedTextBeyondTheLimitsStillLoadsAndMutates(): void {
    $rawName = \str_repeat('&', 60);
    $escapedName = \str_repeat('&amp;', 60);
    $escapedDescription = \str_repeat('&#039;', 150);
    $this->entityManager()->getConnection()->executeStatement(
      'UPDATE "collection" SET name = ?, description = ? WHERE uuid = ?',
      [$escapedName, $escapedDescription, $this->collectionId],
    );
    $thirdImageId = $this->uploadImage($this->ownerToken, false);

    $loaded = $this->reloaded();
    self::assertSame($escapedName, $loaded->getName()->toString());
    self::assertSame($escapedDescription, $loaded->getDescription()->toString());

    $this->clearEntityManager();
    $this->addImageToCollection($this->ownerToken, $this->collectionId, $thirdImageId);
    self::assertSame([$this->firstImageId, $this->secondImageId, $thirdImageId], $this->itemIdsOf($this->reloaded()));

    $this->clearEntityManager();
    $removed = $this->apiRequest(
      'DELETE',
      \sprintf('/api/collection/%s/items/%s', $this->collectionId, $thirdImageId),
      $this->ownerToken,
    );
    self::assertSame(204, $removed);

    self::assertContains($this->rename($rawName), [200, 204]);
    self::assertSame($rawName, $this->reloaded()->getName()->toString());
    self::assertSame('', $this->reloaded()->getDescription()->toString());
  }

  #[Test]
  public function viewBackedCollectionsAndReplayedUsersWorkSideBySideAfterBoot(): void {
    $streamBeforeRename = $this->loggedEvents(ID::fromString($this->ownerId));

    self::assertSame(204, $this->updateDisplayName($this->ownerToken, 'Renamed Owner'));
    self::assertGreaterThan($streamBeforeRename, $this->loggedEvents(ID::fromString($this->ownerId)));

    $newCollectionId = $this->createCollection($this->ownerToken);

    self::assertSame(0, $this->loggedEvents(ID::fromString($newCollectionId)));
    self::assertContains($this->rename('renamed after upgrade'), [200, 204]);
    self::assertSame('renamed after upgrade', $this->reloaded()->getName()->toString());
  }

  private function rename(string $name): int {
    $this->clearEntityManager();

    return $this->apiRequest(
      'PATCH',
      '/api/collection',
      $this->ownerToken,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['id' => $this->collectionId, 'name' => $name, 'description' => ''], JSON_THROW_ON_ERROR),
    );
  }

  private function deleteCollection(): int {
    $this->clearEntityManager();

    return $this->apiRequest(
      'DELETE',
      '/api/collection',
      $this->ownerToken,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['id' => $this->collectionId], JSON_THROW_ON_ERROR),
    );
  }

  private function reloaded(): Collection {
    $this->clearEntityManager();

    /** @var CollectionStoreRepositoryInterface $store */
    $store = static::getContainer()->get(CollectionStoreRepositoryInterface::class);

    return $store->get(ID::fromString($this->collectionId));
  }

  /**
   * @return list<string>
   */
  private function itemIdsOf(Collection $collection): array {
    return \array_map(
      static fn (CollectionItem $item): string => $item->getItemId()->toString(),
      \array_values($collection->getItems()->getSortedItems()),
    );
  }

  private function forgetEventStream(): void {
    $this->eventStore()->executeStatement(
      'DELETE FROM event_store WHERE aggregate_root_id = ?',
      [$this->collectionId],
    );
  }

  private function entityManager(): EntityManagerInterface {
    /** @var EntityManagerInterface $entityManager */
    $entityManager = static::getContainer()->get(EntityManagerInterface::class);

    return $entityManager;
  }

  private function clearEntityManager(): void {
    $this->entityManager()->clear();
  }
}
