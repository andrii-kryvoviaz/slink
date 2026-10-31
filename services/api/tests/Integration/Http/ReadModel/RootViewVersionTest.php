<?php

declare(strict_types=1);

namespace Tests\Integration\Http\ReadModel;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Slink\Bookmark\Infrastructure\ReadModel\View\BookmarkView;
use Slink\Collection\Infrastructure\ReadModel\View\CollectionView;
use Slink\Comment\Infrastructure\ReadModel\View\CommentView;
use Slink\Image\Domain\ValueObject\ImageAttributes;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Notification\Domain\Enum\NotificationType;
use Slink\Notification\Infrastructure\ReadModel\View\NotificationView;
use Slink\Share\Infrastructure\ReadModel\View\ShareView;
use Slink\Tag\Infrastructure\ReadModel\View\TagView;
use Slink\User\Application\Command\UpdateOAuthProvider\UpdateOAuthProviderCommand;
use Slink\User\Infrastructure\ReadModel\View\OAuthProviderView;
use Slink\User\Infrastructure\ReadModel\View\UserView;
use Tests\Integration\Console\Boot\RollsBackToPreviousRelease;
use Tests\Integration\Http\HttpTestCase;

#[Group('sc-1174')]
final class RootViewVersionTest extends HttpTestCase {
  use RollsBackToPreviousRelease;

  /** @var list<string> */
  private const array ROOT_TABLES = [
    'bookmark',
    'collection',
    'comment',
    'image',
    'notification',
    'oauth_provider',
    'share',
    'tag',
    'user',
  ];

  private string $ownerId = '';
  private string $ownerToken = '';
  private string $nonOwnerToken = '';
  private string $imageId = '';
  private string $commentId = '';
  private string $tagId = '';
  private string $collectionId = '';
  private string $shareId = '';
  private string $providerId = '';

  protected function tearDown(): void {
    $this->restorePreviousReleaseBackup();

    parent::tearDown();
  }

  /**
   * @return iterable<string, array{class-string}>
   */
  public static function rootViews(): iterable {
    yield 'user' => [UserView::class];
    yield 'image' => [ImageView::class];
    yield 'share' => [ShareView::class];
    yield 'collection' => [CollectionView::class];
    yield 'comment' => [CommentView::class];
    yield 'tag' => [TagView::class];
    yield 'bookmark' => [BookmarkView::class];
    yield 'notification' => [NotificationView::class];
    yield 'oauth provider' => [OAuthProviderView::class];
  }

  /**
   * @param class-string $viewClass
   */
  #[Test]
  #[DataProvider('rootViews')]
  public function rootViewGuardsItsRowWithAReadOnlyIntegerVersion(string $viewClass): void {
    $metadata = $this->entityManager()->getClassMetadata($viewClass);

    self::assertTrue($metadata->isVersioned);
    self::assertSame('version', $metadata->versionField);
    self::assertSame('integer', $metadata->getTypeOfField('version'));
    self::assertTrue(\method_exists($viewClass, 'getVersion'));
    self::assertFalse(\method_exists($viewClass, 'setVersion'));
  }

  /**
   * @param class-string $viewClass
   */
  #[Test]
  #[DataProvider('rootViews')]
  public function flushingAnUnmodifiedRootViewLeavesItsVersionAlone(string $viewClass): void {
    $this->seedOneRowPerRootTable();
    $selectVersions = \sprintf(
      'SELECT version FROM "%s"',
      $this->entityManager()->getClassMetadata($viewClass)->getTableName(),
    );
    $versionsBeforeFlush = $this->connection()->fetchFirstColumn($selectVersions);

    $this->entityManager()->clear();
    self::assertNotSame([], $this->entityManager()->getRepository($viewClass)->findAll());
    $this->entityManager()->flush();

    self::assertSame($versionsBeforeFlush, $this->connection()->fetchFirstColumn($selectVersions));
  }

  #[Test]
  public function aNewlyProjectedRootViewStartsAtVersionOne(): void {
    $ownerId = $this->createUser('version-owner@local.test', 'versionowner', self::PASSWORD);
    self::assertSame(1, $this->versionOf(UserView::class, $ownerId));

    $this->createUser('version-reader@local.test', 'versionreader', self::PASSWORD);
    $ownerToken = $this->login('versionowner', self::PASSWORD);
    $readerToken = $this->login('versionreader', self::PASSWORD);

    $imageId = $this->uploadImage($ownerToken, true);
    self::assertSame(1, $this->versionOf(ImageView::class, $imageId));

    $commentId = $this->postComment($readerToken, $imageId, 'first look');
    self::assertSame(1, $this->versionOf(CommentView::class, $commentId));
    self::assertSame(1, $this->versionOfOnlyRow(NotificationView::class));

    $this->bookmark($readerToken, $imageId);
    self::assertSame(1, $this->versionOfOnlyRow(BookmarkView::class));

    self::assertSame(1, $this->versionOf(TagView::class, $this->createTag($ownerToken, 'landscape')));
    self::assertSame(1, $this->versionOf(CollectionView::class, $this->createCollection($ownerToken)));
    self::assertSame(1, $this->versionOf(ShareView::class, $this->createImageShare($ownerToken, $imageId)));
    self::assertSame(1, $this->versionOf(OAuthProviderView::class, $this->createOAuthProvider('version-sso')));
  }

  #[Test]
  public function everyOrmUpdateOfATagViewIncrementsItsVersionByOne(): void {
    $this->createUser('version-owner@local.test', 'versionowner', self::PASSWORD);
    $tagId = $this->createTag($this->login('versionowner', self::PASSWORD), 'landscape');

    $this->loadTag($tagId)->setPath('#first-rename');
    $this->entityManager()->flush();
    self::assertSame(2, $this->versionOf(TagView::class, $tagId));

    $this->loadTag($tagId)->setPath('#second-rename');
    $this->entityManager()->flush();
    self::assertSame(3, $this->versionOf(TagView::class, $tagId));
  }

  #[Test]
  public function everyOrmUpdateOfAnImageViewIncrementsItsVersionByOne(): void {
    $this->createUser('version-owner@local.test', 'versionowner', self::PASSWORD);
    $imageId = $this->uploadImage($this->login('versionowner', self::PASSWORD), true);

    $this->describeImage($this->loadImage($imageId), 'first caption');
    $this->entityManager()->flush();
    self::assertSame(2, $this->versionOf(ImageView::class, $imageId));

    $this->describeImage($this->loadImage($imageId), 'second caption');
    $this->entityManager()->flush();
    self::assertSame(3, $this->versionOf(ImageView::class, $imageId));
  }

  #[Test]
  public function everyProjectedUpdateIncrementsTheVersionByOne(): void {
    $this->createUser('version-owner@local.test', 'versionowner', self::PASSWORD);
    $token = $this->login('versionowner', self::PASSWORD);
    $imageId = $this->uploadImage($token, true);

    $this->captionImage($token, $imageId, 'first caption');
    self::assertSame(2, $this->versionOf(ImageView::class, $imageId));

    $this->captionImage($token, $imageId, 'second caption');
    self::assertSame(3, $this->versionOf(ImageView::class, $imageId));
  }

  #[Test]
  public function flushingAStaleTagViewFailsAndKeepsTheOtherWritersValues(): void {
    $this->createUser('version-owner@local.test', 'versionowner', self::PASSWORD);
    $tagId = $this->createTag($this->login('versionowner', self::PASSWORD), 'landscape');
    $staleTag = $this->loadTag($tagId);

    $this->connection()->executeStatement(
      'UPDATE "tag" SET path = ?, version = version + 1 WHERE uuid = ?',
      ['#other-writer', $tagId],
    );
    $staleTag->setPath('#stale-writer');

    self::assertInstanceOf(OptimisticLockException::class, $this->flushFailure());
    self::assertSame(
      ['path' => '#other-writer', 'version' => 2],
      $this->connection()->fetchAssociative('SELECT path, version FROM "tag" WHERE uuid = ?', [$tagId]),
    );
  }

  #[Test]
  public function flushingAStaleImageViewFailsAndKeepsTheOtherWritersValues(): void {
    $this->createUser('version-owner@local.test', 'versionowner', self::PASSWORD);
    $imageId = $this->uploadImage($this->login('versionowner', self::PASSWORD), true);
    $staleImage = $this->loadImage($imageId);

    $this->connection()->executeStatement(
      'UPDATE "image" SET description = ?, version = version + 1 WHERE uuid = ?',
      ['other writer caption', $imageId],
    );
    $this->describeImage($staleImage, 'stale writer caption');

    self::assertInstanceOf(OptimisticLockException::class, $this->flushFailure());
    self::assertSame(
      ['description' => 'other writer caption', 'version' => 2],
      $this->connection()->fetchAssociative('SELECT description, version FROM "image" WHERE uuid = ?', [$imageId]),
    );
  }

  #[Test]
  public function bulkMarkAllAsReadLeavesNotificationVersionsAlone(): void {
    $this->seedOneRowPerRootTable();

    self::assertContains($this->apiRequest('POST', '/api/notifications/mark-all-read', $this->ownerToken), [200, 204]);

    $notification = $this->commentNotification();
    self::assertTrue($notification->getIsRead());
    self::assertSame(1, $notification->getVersion());
  }

  #[Test]
  public function bulkDescendantPathRewriteLeavesChildVersionsAlone(): void {
    $this->createUser('version-owner@local.test', 'versionowner', self::PASSWORD);
    $token = $this->login('versionowner', self::PASSWORD);
    $parentId = $this->createTag($token, 'parent');
    $childId = $this->createTag($token, 'child', $parentId);
    $newParentId = $this->createTag($token, 'newparent');
    $pathBeforeMove = $this->loadTag($childId)->getPath();

    $this->moveTag($token, $parentId, $newParentId);

    self::assertNotSame($pathBeforeMove, $this->loadTag($childId)->getPath());
    self::assertSame(1, $this->versionOf(TagView::class, $childId));
  }

  #[Test]
  public function onlyTheNineRootTablesCarryAVersionColumn(): void {
    self::assertSame(
      \array_map(
        static fn (string $table): array => ['table' => $table, 'type' => 'INTEGER', 'notnull' => 1, 'default' => '1'],
        self::ROOT_TABLES,
      ),
      $this->connection()->fetchAllAssociative(
        <<<'SQL'
          SELECT t.name AS "table", c.type AS "type", c."notnull" AS "notnull", c.dflt_value AS "default"
          FROM sqlite_master t, pragma_table_info(t.name) c
          WHERE t.type = 'table' AND c.name = 'version' AND t.name NOT LIKE 'doctrine_migration%' AND t.name NOT LIKE 'data_migration%'
          ORDER BY t.name
          SQL,
      ),
    );
  }

  #[Test]
  public function bootUpgradesAPreviousReleaseDatabaseWithExistingRowsAtVersionOne(): void {
    $this->seedOneRowPerRootTable();
    $rowsBeforeUpgrade = $this->rootRows();

    $this->upgradeFromPreviousRelease();

    self::assertSame($rowsBeforeUpgrade, $this->rootRows());

    foreach (self::ROOT_TABLES as $table) {
      self::assertSame(
        [1],
        $this->connection()->fetchFirstColumn(\sprintf('SELECT DISTINCT version FROM "%s"', $table)),
        $table,
      );
    }
  }

  #[Test]
  public function rowsFromAPreviousReleaseKeepUpdatingThroughTheirProjections(): void {
    $this->seedOneRowPerRootTable();
    $this->upgradeFromPreviousRelease();

    $this->captionImage($this->ownerToken, $this->imageId, 'caption after upgrade');
    self::assertSame(2, $this->versionOf(ImageView::class, $this->imageId));

    self::assertContains($this->updateComment($this->nonOwnerToken, $this->commentId), [200, 204]);
    self::assertSame(2, $this->versionOf(CommentView::class, $this->commentId));

    self::assertContains($this->updateCollection($this->ownerToken, $this->collectionId), [200, 204]);
    self::assertSame(2, $this->versionOf(CollectionView::class, $this->collectionId));

    $this->setSharePassword($this->ownerToken, $this->shareId, self::SHARE_PASSWORD);
    self::assertSame(2, $this->versionOf(ShareView::class, $this->shareId));

    self::assertContains($this->updateDisplayName($this->ownerToken, 'Upgraded Owner'), [200, 204]);
    self::assertSame(2, $this->versionOf(UserView::class, $this->ownerId));

    $this->moveTag($this->ownerToken, $this->tagId, $this->createTag($this->ownerToken, 'upgrade-parent'));
    self::assertSame(2, $this->versionOf(TagView::class, $this->tagId));

    $this->commandBus()->handleSync(
      new UpdateOAuthProviderCommand(enabled: false)->withContext(['id' => $this->providerId]),
    );
    self::assertSame(2, $this->versionOf(OAuthProviderView::class, $this->providerId));

    self::assertContains(
      $this->apiRequest('POST', \sprintf('/api/notifications/%s/read', $this->commentNotification()->getId()), $this->ownerToken),
      [200, 204],
    );
    self::assertSame(2, $this->commentNotification()->getVersion());
  }

  #[Test]
  public function requestsOverAPreviousReleaseDatabaseKeepSucceedingAfterBoot(): void {
    $this->seedOneRowPerRootTable();
    $this->upgradeFromPreviousRelease();

    self::assertSame(200, $this->apiRequest('GET', \sprintf('/api/image/%s/detail', $this->imageId), $this->ownerToken));
    self::assertSame(200, $this->apiRequest('GET', \sprintf('/api/image/%s/comments', $this->imageId), $this->ownerToken));
    self::assertSame(200, $this->apiRequest('GET', '/api/bookmarks', $this->nonOwnerToken));
    self::assertSame(200, $this->apiRequest('GET', '/api/collections', $this->ownerToken));
    self::assertSame(200, $this->apiRequest('GET', '/api/notifications', $this->ownerToken));
    self::assertSame(200, $this->apiRequest('GET', '/api/tags', $this->ownerToken));
    self::assertContains($this->apiRequest('DELETE', \sprintf('/api/image/%s/bookmark', $this->imageId), $this->nonOwnerToken), [200, 204]);
    self::assertContains(
      $this->apiRequest('DELETE', \sprintf('/api/comment/%s', $this->commentId), $this->nonOwnerToken),
      [200, 202, 204],
    );
  }

  private function entityManager(): EntityManagerInterface {
    /** @var EntityManagerInterface $entityManager */
    $entityManager = static::getContainer()->get(EntityManagerInterface::class);

    return $entityManager;
  }

  /**
   * @param class-string<UserView|ImageView|ShareView|CollectionView|CommentView|TagView|OAuthProviderView> $viewClass
   */
  private function versionOf(string $viewClass, string $id): int {
    $this->entityManager()->clear();
    $view = $this->entityManager()->find($viewClass, $id);
    self::assertInstanceOf($viewClass, $view);

    return $view->getVersion();
  }

  /**
   * @param class-string<NotificationView|BookmarkView> $viewClass
   */
  private function versionOfOnlyRow(string $viewClass): int {
    $this->entityManager()->clear();
    $views = $this->entityManager()->getRepository($viewClass)->findAll();
    self::assertCount(1, $views);

    return $views[0]->getVersion();
  }

  private function commentNotification(): NotificationView {
    $this->entityManager()->clear();
    $notification = $this->entityManager()->getRepository(NotificationView::class)->findOneBy(['type' => NotificationType::COMMENT]);
    self::assertInstanceOf(NotificationView::class, $notification);

    return $notification;
  }

  private function loadTag(string $tagId): TagView {
    $this->entityManager()->clear();
    $tag = $this->entityManager()->find(TagView::class, $tagId);
    self::assertInstanceOf(TagView::class, $tag);

    return $tag;
  }

  private function loadImage(string $imageId): ImageView {
    $this->entityManager()->clear();
    $image = $this->entityManager()->find(ImageView::class, $imageId);
    self::assertInstanceOf(ImageView::class, $image);

    return $image;
  }

  private function describeImage(ImageView $image, string $description): void {
    $image->updateAttributes(ImageAttributes::create($image->getFileName(), $description, true));
  }

  private function flushFailure(): ?\Throwable {
    try {
      $this->entityManager()->flush();
    } catch (\Throwable $failure) {
      return $failure;
    }

    return null;
  }

  private function createTag(string $token, string $name, ?string $parentId = null): string {
    $status = $this->apiRequest(
      'POST',
      '/api/tags',
      $token,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['name' => $name, 'parentId' => $parentId], JSON_THROW_ON_ERROR),
    );

    self::assertSame(201, $status, 'Create tag failed: ' . (string) $this->client->getResponse()->getContent());

    return $this->extractId((string) $this->client->getResponse()->getContent());
  }

  private function moveTag(string $token, string $tagId, string $newParentId): void {
    $status = $this->apiRequest(
      'PATCH',
      '/api/tags/move',
      $token,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['id' => $tagId, 'newParentId' => $newParentId], JSON_THROW_ON_ERROR),
    );

    self::assertContains($status, [200, 204], 'Move tag failed: ' . (string) $this->client->getResponse()->getContent());
  }

  private function bookmark(string $token, string $imageId): void {
    self::assertContains(
      $this->apiRequest('POST', \sprintf('/api/image/%s/bookmark', $imageId), $token),
      [200, 201, 204],
    );
  }

  private function captionImage(string $token, string $imageId, string $description): void {
    $status = $this->apiRequest(
      'PATCH',
      \sprintf('/api/image/%s', $imageId),
      $token,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['description' => $description], JSON_THROW_ON_ERROR),
    );

    self::assertContains($status, [200, 204], 'Update image failed: ' . (string) $this->client->getResponse()->getContent());
  }

  private function updateComment(string $token, string $commentId): int {
    return $this->apiRequest(
      'PATCH',
      \sprintf('/api/comment/%s', $commentId),
      $token,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['content' => 'edited after upgrade'], JSON_THROW_ON_ERROR),
    );
  }

  private function updateCollection(string $token, string $collectionId): int {
    return $this->apiRequest(
      'PATCH',
      '/api/collection',
      $token,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['id' => $collectionId, 'name' => 'renamed after upgrade', 'description' => ''], JSON_THROW_ON_ERROR),
    );
  }

  private function seedOneRowPerRootTable(): void {
    $this->ownerId = $this->createUser('version-owner@local.test', 'versionowner', self::PASSWORD);
    $this->createUser('version-reader@local.test', 'versionreader', self::PASSWORD);
    $this->ownerToken = $this->login('versionowner', self::PASSWORD);
    $this->nonOwnerToken = $this->login('versionreader', self::PASSWORD);
    $this->imageId = $this->uploadImage($this->ownerToken, true);
    $this->commentId = $this->postComment($this->nonOwnerToken, $this->imageId, 'first look');
    $this->bookmark($this->nonOwnerToken, $this->imageId);
    $this->tagId = $this->createTag($this->ownerToken, 'landscape');
    $this->collectionId = $this->createCollection($this->ownerToken);
    $this->shareId = $this->createImageShare($this->ownerToken, $this->imageId);
    $this->providerId = $this->createOAuthProvider('version-sso');
  }

  private function upgradeFromPreviousRelease(): void {
    $this->rollBackToPreviousRelease();
    $this->bootApplicationOnPreviousReleaseDatabase();

    self::assertSame(self::ROOT_TABLES, $this->tablesWithVersionColumn());
  }

  /**
   * @return array<string, list<array<string, mixed>>>
   */
  private function rootRows(): array {
    $rows = [];

    foreach (self::ROOT_TABLES as $table) {
      $rows[$table] = \array_map(
        static fn (array $row): array => \array_diff_key($row, ['version' => true]),
        $this->connection()->fetchAllAssociative(\sprintf('SELECT * FROM "%s" ORDER BY 1', $table)),
      );
    }

    return $rows;
  }
}
