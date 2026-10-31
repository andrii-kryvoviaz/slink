<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Store;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Slink\Bookmark\Domain\Repository\BookmarkStoreRepositoryInterface;
use Slink\Comment\Domain\Repository\CommentStoreRepositoryInterface;
use Slink\Notification\Domain\Repository\NotificationStoreRepositoryInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\User\Domain\Repository\OAuthProviderStoreRepositoryInterface;
use Tests\Integration\Console\Boot\RollsBackToPreviousRelease;
use Tests\Integration\Http\HttpTestCase;

#[Group('sc-1176')]
final class FlatStoresUpgradeTest extends HttpTestCase {
  use InspectsEventLog;
  use RollsBackToPreviousRelease;

  private string $adminToken = '';
  private string $ownerId = '';
  private string $ownerToken = '';
  private string $readerToken = '';
  private string $imageId = '';
  private string $commentId = '';
  private string $bookmarkId = '';
  private string $notificationId = '';
  private string $providerId = '';

  protected function setUp(): void {
    parent::setUp();

    $this->adminToken = $this->bootAdmin();
    $this->ownerId = $this->createUser('flat-upgrade-owner@local.test', 'flatupgradeowner', self::PASSWORD);
    $this->createUser('flat-upgrade-reader@local.test', 'flatupgradereader', self::PASSWORD);
    $this->ownerToken = $this->login('flatupgradeowner', self::PASSWORD);
    $this->readerToken = $this->login('flatupgradereader', self::PASSWORD);
    $this->imageId = $this->uploadImage($this->ownerToken, true);
    $this->commentId = $this->postComment($this->readerToken, $this->imageId, 'Tom & Jerry');
    self::assertContains(
      $this->apiRequest('POST', \sprintf('/api/image/%s/bookmark', $this->imageId), $this->readerToken),
      [200, 201],
    );
    $this->bookmarkId = $this->singleRowId('bookmark');
    $this->notificationId = $this->notificationIdOfType('comment');
    $this->providerId = $this->createOAuthProvider('flat-upgrade');

    $this->rollBackToPreviousRelease();
    $this->bootApplicationOnPreviousReleaseDatabase();
  }

  protected function tearDown(): void {
    $this->restorePreviousReleaseBackup();

    parent::tearDown();
  }

  /**
   * @param array<string, string> $server
   */
  #[\Override]
  protected function apiRequest(
    string $method,
    string $path,
    ?string $token = null,
    array $server = [],
    ?string $body = null,
  ): int {
    $this->entityManager()->clear();

    return parent::apiRequest($method, $path, $token, $server, $body);
  }

  #[Test]
  public function aBookmarkFromThePreviousReleaseRemovesAndIsThenNotFound(): void {
    $this->forgetEventStreams();

    self::assertContains(
      $this->apiRequest('DELETE', \sprintf('/api/image/%s/bookmark', $this->imageId), $this->readerToken),
      [200, 204],
    );

    self::assertSame(0, $this->countRowsByColumn('bookmark', 'uuid', $this->bookmarkId));
    $this->assertNotFound(fn () => $this->bookmarks()->get(ID::fromString($this->bookmarkId)));
  }

  #[Test]
  public function aCommentFromThePreviousReleaseEditsWithoutEscapingAgain(): void {
    $this->forgetEventStreams();
    $storedContent = $this->connection()->fetchOne('SELECT content FROM "comment" WHERE uuid = ?', [$this->commentId]);

    self::assertSame($storedContent, $this->comments()->get(ID::fromString($this->commentId))->getContent()->toString());

    self::assertContains($this->editComment('Tom & Jerry edited'), [200, 204]);

    self::assertSame(
      \str_replace('Jerry', 'Jerry edited', (string) $storedContent),
      $this->connection()->fetchOne('SELECT content FROM "comment" WHERE uuid = ?', [$this->commentId]),
    );
  }

  #[Test]
  public function aCommentFromThePreviousReleaseDeletes(): void {
    $this->forgetEventStreams();

    self::assertContains(
      $this->apiRequest('DELETE', \sprintf('/api/comment/%s', $this->commentId), $this->readerToken),
      [200, 202, 204],
    );

    $this->entityManager()->clear();
    self::assertTrue($this->comments()->get(ID::fromString($this->commentId))->isDeleted());
  }

  #[Test]
  public function aNotificationFromThePreviousReleaseMarksRead(): void {
    $this->forgetEventStreams();

    self::assertContains(
      $this->apiRequest('POST', \sprintf('/api/notifications/%s/read', $this->notificationId), $this->ownerToken),
      [200, 204],
    );

    $this->entityManager()->clear();
    $notification = $this->notifications()->get(ID::fromString($this->notificationId));
    self::assertTrue($notification->isRead());
    self::assertSame($this->ownerId, $notification->getUserId()->toString());
    self::assertSame($this->imageId, $notification->getReferenceId()->toString());
  }

  #[Test]
  public function aProviderFromThePreviousReleaseUpdatesKeepingItsEncryptedCredentials(): void {
    $this->forgetEventStreams();
    $credentialsSql = 'SELECT client_id, client_secret FROM "oauth_provider" WHERE id = ?';
    $encryptedBefore = $this->connection()->fetchAssociative($credentialsSql, [$this->providerId]);

    self::assertSame(204, $this->updateProvider());

    self::assertSame($encryptedBefore, $this->connection()->fetchAssociative($credentialsSql, [$this->providerId]));
    self::assertSame(
      'Renamed after upgrade',
      $this->connection()->fetchOne('SELECT name FROM "oauth_provider" WHERE id = ?', [$this->providerId]),
    );
  }

  #[Test]
  public function aProviderFromThePreviousReleaseRemovesAndIsThenNotFound(): void {
    $this->forgetEventStreams();

    self::assertSame(204, $this->apiRequest(
      'DELETE',
      \sprintf('/api/admin/oauth/providers/%s', $this->providerId),
      $this->adminToken,
    ));

    $this->assertNotFound(fn () => $this->providers()->get(ID::fromString($this->providerId)));
  }

  #[Test]
  public function everyReadOfThePreviousReleaseRowsKeepsSucceeding(): void {
    self::assertSame(200, $this->apiRequest('GET', \sprintf('/api/image/%s/comments', $this->imageId), $this->ownerToken));
    self::assertSame(200, $this->apiRequest('GET', '/api/bookmarks', $this->readerToken));
    self::assertSame(200, $this->apiRequest('GET', \sprintf('/api/image/%s/bookmarkers', $this->imageId), $this->ownerToken));
    self::assertSame(200, $this->apiRequest('GET', '/api/notifications', $this->ownerToken));
    self::assertSame(200, $this->apiRequest('GET', '/api/notifications/unread-count', $this->ownerToken));
    self::assertSame(200, $this->apiRequest('GET', '/api/admin/oauth/providers', $this->adminToken));
    self::assertSame(200, $this->apiRequest('GET', '/api/auth/sso/providers'));
  }

  #[Test]
  public function viewBackedStoresAndReplayedUsersWorkSideBySideAfterBoot(): void {
    $owner = ID::fromString($this->ownerId);
    $streamBeforeRename = $this->loggedEvents($owner);

    self::assertSame(204, $this->updateDisplayName($this->ownerToken, 'Renamed Owner'));
    $newCommentId = $this->postComment($this->readerToken, $this->imageId, 'after the upgrade');

    self::assertGreaterThan($streamBeforeRename, $this->loggedEvents($owner));
    self::assertSame(0, $this->loggedEvents(ID::fromString($newCommentId)));
  }

  /**
   * @param callable(): object $load
   */
  private function assertNotFound(callable $load): void {
    $this->entityManager()->clear();

    try {
      $load();
    } catch (NotFoundException) {
      $this->addToAssertionCount(1);

      return;
    }

    self::fail('Expected the store to report the row as not found.');
  }

  private function editComment(string $content): int {
    return $this->apiRequest(
      'PATCH',
      \sprintf('/api/comment/%s', $this->commentId),
      $this->readerToken,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['content' => $content], JSON_THROW_ON_ERROR),
    );
  }

  private function updateProvider(): int {
    return $this->apiRequest(
      'PUT',
      \sprintf('/api/admin/oauth/providers/%s', $this->providerId),
      $this->adminToken,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['name' => 'Renamed after upgrade'], JSON_THROW_ON_ERROR),
    );
  }

  private function forgetEventStreams(): void {
    foreach ([$this->bookmarkId, $this->commentId, $this->notificationId, $this->providerId] as $aggregateId) {
      $this->eventStore()->executeStatement('DELETE FROM event_store WHERE aggregate_root_id = ?', [$aggregateId]);
      $this->eventStore()->executeStatement('DELETE FROM snapshots WHERE aggregate_root_id = ?', [$aggregateId]);
    }
  }

  private function singleRowId(string $table): string {
    $id = $this->connection()->fetchOne(\sprintf('SELECT uuid FROM "%s"', $table));
    self::assertIsString($id);

    return $id;
  }

  private function notificationIdOfType(string $type): string {
    $id = $this->connection()->fetchOne('SELECT uuid FROM "notification" WHERE type = ?', [$type]);
    self::assertIsString($id);

    return $id;
  }

  private function entityManager(): EntityManagerInterface {
    /** @var EntityManagerInterface $entityManager */
    $entityManager = static::getContainer()->get(EntityManagerInterface::class);

    return $entityManager;
  }

  private function bookmarks(): BookmarkStoreRepositoryInterface {
    /** @var BookmarkStoreRepositoryInterface $store */
    $store = static::getContainer()->get(BookmarkStoreRepositoryInterface::class);

    return $store;
  }

  private function comments(): CommentStoreRepositoryInterface {
    /** @var CommentStoreRepositoryInterface $store */
    $store = static::getContainer()->get(CommentStoreRepositoryInterface::class);

    return $store;
  }

  private function notifications(): NotificationStoreRepositoryInterface {
    /** @var NotificationStoreRepositoryInterface $store */
    $store = static::getContainer()->get(NotificationStoreRepositoryInterface::class);

    return $store;
  }

  private function providers(): OAuthProviderStoreRepositoryInterface {
    /** @var OAuthProviderStoreRepositoryInterface $store */
    $store = static::getContainer()->get(OAuthProviderStoreRepositoryInterface::class);

    return $store;
  }
}
