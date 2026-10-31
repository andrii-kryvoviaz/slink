<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Tag;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Tag\Domain\Repository\TagStoreRepositoryInterface;
use Tests\Integration\Console\Boot\RollsBackToPreviousRelease;
use Tests\Integration\Http\HttpTestCase;
use Tests\Integration\Http\Store\InspectsEventLog;

#[Group('sc-1175')]
final class TagStoreUpgradeTest extends HttpTestCase {
  use InspectsEventLog;
  use RollsBackToPreviousRelease;

  private string $ownerToken = '';
  private string $parentId = '';
  private string $childId = '';
  private string $bucketId = '';
  private string $collectionId = '';

  protected function setUp(): void {
    parent::setUp();

    $this->createUser('tag-upgrade-owner@local.test', 'tagupgradeowner', self::PASSWORD);
    $this->ownerToken = $this->login('tagupgradeowner', self::PASSWORD);
    $this->parentId = $this->createTag('parent');
    $this->childId = $this->createTag('child', $this->parentId);
    $this->bucketId = $this->createTag('bucket');
    $this->collectionId = $this->createCollection($this->ownerToken);

    $this->rollBackToPreviousRelease();
    $this->bootApplicationOnPreviousReleaseDatabase();
  }

  protected function tearDown(): void {
    $this->restorePreviousReleaseBackup();

    parent::tearDown();
  }

  #[Test]
  public function aTagFromThePreviousReleaseMovesAndItsDescendantReloadsTheNewPath(): void {
    $this->forgetEventStreams();

    self::assertSame(204, $this->moveTag($this->parentId, $this->bucketId));

    self::assertSame('#bucket/parent/child', $this->reloadedPath($this->childId));
  }

  #[Test]
  public function aTagFromThePreviousReleaseTakesNewChildren(): void {
    $this->forgetEventStreams();

    $newChildId = $this->createTag('new-child', $this->parentId);

    self::assertSame('#parent/new-child', $this->reloadedPath($newChildId));
  }

  #[Test]
  public function aTagFromThePreviousReleaseDeletesWithItsDescendants(): void {
    $this->forgetEventStreams();

    self::assertSame(204, $this->apiRequest('DELETE', \sprintf('/api/tags/%s', $this->parentId), $this->ownerToken));

    self::assertSame(0, $this->countRowsByColumn('tag', 'uuid', $this->parentId));
    self::assertSame(0, $this->countRowsByColumn('tag', 'uuid', $this->childId));
  }

  #[Test]
  public function aTagFromThePreviousReleaseKeepsLabellingImages(): void {
    $this->forgetEventStreams();
    $imageId = $this->uploadImage($this->ownerToken, true);
    $tagPath = \sprintf('/api/images/%s/tags/%s', $imageId, $this->childId);

    self::assertContains($this->apiRequest('POST', $tagPath, $this->ownerToken), [200, 201, 204]);
    self::assertSame(200, $this->apiRequest('GET', \sprintf('/api/images/%s/tags', $imageId), $this->ownerToken));
    self::assertStringContainsString($this->childId, (string) $this->client->getResponse()->getContent());
    self::assertContains($this->apiRequest('DELETE', $tagPath, $this->ownerToken), [200, 204]);
  }

  #[Test]
  public function viewBackedTagsAndReplayedCollectionsWorkSideBySideAfterBoot(): void {
    $streamBeforeRename = $this->loggedEvents(ID::fromString($this->collectionId));

    self::assertSame(200, $this->apiRequest('GET', '/api/tags', $this->ownerToken));
    self::assertSame(200, $this->apiRequest('GET', \sprintf('/api/tags/%s', $this->childId), $this->ownerToken));
    self::assertContains(
      $this->apiRequest(
        'PATCH',
        '/api/collection',
        $this->ownerToken,
        ['CONTENT_TYPE' => 'application/json'],
        \json_encode(['id' => $this->collectionId, 'name' => 'renamed after upgrade', 'description' => ''], JSON_THROW_ON_ERROR),
      ),
      [200, 204],
    );
    $newTagId = $this->createTag('after-upgrade');

    self::assertSame(
      'renamed after upgrade',
      $this->connection()->fetchOne('SELECT name FROM "collection" WHERE uuid = ?', [$this->collectionId]),
    );
    self::assertGreaterThan($streamBeforeRename, $this->loggedEvents(ID::fromString($this->collectionId)));
    self::assertSame(0, $this->loggedEvents(ID::fromString($newTagId)));
  }

  private function forgetEventStreams(): void {
    $this->eventStore()->executeStatement('DELETE FROM event_store WHERE aggregate_root_id IN (?, ?, ?)', [
      $this->parentId,
      $this->childId,
      $this->bucketId,
    ]);
  }

  private function createTag(string $name, ?string $parentId = null): string {
    $this->clearEntityManager();
    $status = $this->apiRequest(
      'POST',
      '/api/tags',
      $this->ownerToken,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['name' => $name, 'parentId' => $parentId], JSON_THROW_ON_ERROR),
    );

    self::assertSame(201, $status, 'Create tag failed: ' . (string) $this->client->getResponse()->getContent());

    return $this->extractId((string) $this->client->getResponse()->getContent());
  }

  private function moveTag(string $tagId, string $newParentId): int {
    $this->clearEntityManager();

    return $this->apiRequest(
      'PATCH',
      '/api/tags/move',
      $this->ownerToken,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['id' => $tagId, 'newParentId' => $newParentId], JSON_THROW_ON_ERROR),
    );
  }

  private function reloadedPath(string $tagId): string {
    $this->clearEntityManager();

    /** @var TagStoreRepositoryInterface $store */
    $store = static::getContainer()->get(TagStoreRepositoryInterface::class);

    return $store->get(ID::fromString($tagId))->getPath()->getValue();
  }

  private function clearEntityManager(): void {
    /** @var EntityManagerInterface $entityManager */
    $entityManager = static::getContainer()->get(EntityManagerInterface::class);
    $entityManager->clear();
  }
}
