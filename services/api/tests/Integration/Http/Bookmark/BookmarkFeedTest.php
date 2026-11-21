<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Bookmark;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\Integration\Http\HttpTestCase;
use Tests\Integration\Http\Image\DrivesFeedDelivery;

#[Group('feed-delivery')]
final class BookmarkFeedTest extends HttpTestCase {
  use DrivesFeedDelivery;

  private string $ownerToken = '';
  private string $readerToken = '';
  private string $otherReaderToken = '';
  private string $imageId = '';

  protected function setUp(): void {
    parent::setUp();

    $this->recordFeedPublications();
    $this->createUser('feed-bookmark-owner@local.test', 'feedbookmarkowner', self::PASSWORD);
    $this->createUser('feed-bookmark-reader@local.test', 'feedbookmarkreader', self::PASSWORD);
    $this->createUser('feed-bookmark-other@local.test', 'feedbookmarkother', self::PASSWORD);
    $this->ownerToken = $this->login('feedbookmarkowner', self::PASSWORD);
    $this->readerToken = $this->login('feedbookmarkreader', self::PASSWORD);
    $this->otherReaderToken = $this->login('feedbookmarkother', self::PASSWORD);
    $this->imageId = $this->uploadImage($this->ownerToken, true);
    $this->consumeOne();
  }

  private function bookmark(string $token): void {
    self::assertSame(201, $this->apiRequest('POST', \sprintf('/api/image/%s/bookmark', $this->imageId), $token));
  }

  private function unbookmark(string $token): void {
    self::assertContains($this->apiRequest('DELETE', \sprintf('/api/image/%s/bookmark', $this->imageId), $token), [200, 204]);
  }

  private function publishedBookmarkCount(): mixed {
    $message = \array_last($this->publisher->messages);
    self::assertNotNull($message);

    return $message['data']['image']['bookmarkCount'] ?? null;
  }

  #[Test]
  public function aBookmarkQueuesExactlyOneImageUpdatedCarryingTheNewCount(): void {
    $this->bookmark($this->readerToken);

    self::assertSame(1, $this->pending());
    $this->consumeOne();
    $this->assertPublished('image_updated', $this->imageId);
    self::assertSame(1, $this->publishedBookmarkCount());
    self::assertSame(0, $this->pending());

    $this->bookmark($this->otherReaderToken);

    self::assertSame(1, $this->pending());
    $this->consumeOne();
    $this->assertPublished('image_updated', $this->imageId);
    self::assertSame(2, $this->publishedBookmarkCount());
  }

  #[Test]
  public function aRemovedBookmarkQueuesExactlyOneImageUpdatedCarryingTheLowerCount(): void {
    $this->bookmark($this->readerToken);
    $this->bookmark($this->otherReaderToken);
    $this->discardQueuedMessages();

    $this->unbookmark($this->readerToken);

    self::assertSame(1, $this->pending());
    $this->consumeOne();
    $this->assertPublished('image_updated', $this->imageId);
    self::assertSame(1, $this->publishedBookmarkCount());
    self::assertSame(0, $this->pending());
  }

  #[Test]
  public function aBookmarkRemovedFromAnImageThatWentPrivateQueuesNothing(): void {
    $this->bookmark($this->readerToken);
    $this->hide();
    $this->discardQueuedMessages();

    $this->unbookmark($this->readerToken);

    self::assertSame(0, $this->pending());
  }

  #[Test]
  public function deletingAnImageHoldingBookmarksQueuesExactlyOneRemoval(): void {
    $this->bookmark($this->readerToken);
    $this->bookmark($this->otherReaderToken);
    $this->discardQueuedMessages();

    self::assertContains($this->apiRequest('DELETE', '/api/image/' . $this->imageId, $this->ownerToken), [200, 204]);

    self::assertSame(1, $this->pending());
    $this->consumeOne();
    $this->assertPublished('image_removed', $this->imageId);
    self::assertSame(0, $this->pending());
  }

  #[Test]
  public function purgingABookmarkerQueuesNoFeedMessage(): void {
    $adminToken = $this->bootAdmin();
    $readerId = $this->userIdOf('feedbookmarkreader');
    $this->bookmark($this->readerToken);
    $this->discardQueuedMessages();

    self::assertSame(204, $this->apiRequest('DELETE', '/api/user/' . $readerId, $adminToken));

    self::assertSame(0, $this->pending());
  }

  private function discardQueuedMessages(): void {
    $this->connection()->executeStatement("DELETE FROM message_queue WHERE queue_name = 'feed'");
  }

  private function hide(): void {
    $status = $this->apiRequest(
      'PATCH',
      '/api/image/' . $this->imageId,
      $this->ownerToken,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['isPublic' => false, 'description' => null], JSON_THROW_ON_ERROR),
    );

    self::assertContains($status, [200, 204]);
  }

  private function userIdOf(string $username): string {
    return (string) $this->connection()->fetchOne('SELECT uuid FROM "user" WHERE username = ?', [$username]);
  }
}
