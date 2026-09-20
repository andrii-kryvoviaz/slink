<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Notification;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\Integration\Http\HttpTestCase;

final class GetNotificationsFilterTest extends HttpTestCase {
  private const int NOTIFICATION_COUNT = 7;

  private string $ownerToken = '';

  private string $firstReaderToken = '';

  private function seedOwnerNotifications(): void {
    $this->createUser('filter-owner@local.test', 'filterowner', self::PASSWORD);
    $this->createUser('filter-first-reader@local.test', 'filterfirstreader', self::PASSWORD);
    $this->createUser('filter-second-reader@local.test', 'filtersecondreader', self::PASSWORD);
    $this->ownerToken = $this->login('filterowner', self::PASSWORD);
    $this->firstReaderToken = $this->login('filterfirstreader', self::PASSWORD);
    $secondReaderToken = $this->login('filtersecondreader', self::PASSWORD);

    $imageId = $this->uploadImage($this->ownerToken, true);
    $ownerCommentId = $this->postComment($this->ownerToken, $imageId, 'owner note');

    foreach ([$this->firstReaderToken, $secondReaderToken] as $readerToken) {
      self::assertContains(
        $this->apiRequest('POST', \sprintf('/api/image/%s/bookmark', $imageId), $readerToken),
        [200, 201, 204],
        'Reader failed to bookmark.',
      );
      $this->postComment($readerToken, $imageId, 'reader comment');
    }

    $this->postComment($this->firstReaderToken, $imageId, 'first reply', $ownerCommentId);
    $this->postComment($secondReaderToken, $imageId, 'second reply', $ownerCommentId);
    $this->postComment($secondReaderToken, $imageId, 'third reply', $ownerCommentId);

    self::assertSame(self::NOTIFICATION_COUNT, $this->fetchPage('?limit=100')['meta']['total'], 'Seeding must produce seven owner notifications.');
  }

  /**
   * @return array{meta: array{page: int, size: int, total: int}, data: list<array{id: string, type: string, isRead: bool, createdAt: array{timestamp: int}}>}
   */
  private function fetchPage(string $query, ?string $token = null): array {
    $status = $this->apiRequest('GET', '/api/notifications' . $query, $token ?? $this->ownerToken);
    self::assertSame(200, $status, 'Fetch notifications failed: ' . (string) $this->client->getResponse()->getContent());

    /** @var array{meta: array{page: int, size: int, total: int}, data: list<array{id: string, type: string, isRead: bool, createdAt: array{timestamp: int}}>} $payload */
    $payload = $this->responsePayload();

    return ['meta' => $payload['meta'], 'data' => $payload['data']];
  }

  /**
   * @return iterable<string, array{string, int}>
   */
  public static function typeTotals(): iterable {
    yield 'bookmarks' => ['added_to_bookmarks', 2];
    yield 'comments' => ['comment', 2];
    yield 'replies' => ['comment_reply', 3];
  }

  #[Test]
  #[DataProvider('typeTotals')]
  public function filtersByType(string $type, int $expectedTotal): void {
    $this->seedOwnerNotifications();

    $page = $this->fetchPage('?limit=100&type=' . $type);

    self::assertSame($expectedTotal, $page['meta']['total']);
    self::assertCount($expectedTotal, $page['data']);
    self::assertSame([$type], \array_values(\array_unique(\array_column($page['data'], 'type'))));
  }

  #[Test]
  public function typeTotalsAddUpToTheUnfilteredTotal(): void {
    $this->seedOwnerNotifications();

    $sum = 0;
    foreach (self::typeTotals() as [$type]) {
      $sum += $this->fetchPage('?limit=100&type=' . $type)['meta']['total'];
    }

    self::assertSame(self::NOTIFICATION_COUNT, $sum);
  }

  #[Test]
  public function keepsNewestFirstWithinAType(): void {
    $this->seedOwnerNotifications();

    $timestamps = \array_column(\array_column($this->fetchPage('?limit=100&type=comment_reply')['data'], 'createdAt'), 'timestamp');
    $sorted = $timestamps;
    \rsort($sorted);

    self::assertSame($sorted, $timestamps);
  }

  #[Test]
  public function unreadOnlyExcludesReadNotifications(): void {
    $this->seedOwnerNotifications();

    $beforeRead = $this->fetchPage('?limit=100&unread=true');
    self::assertSame(self::NOTIFICATION_COUNT, $beforeRead['meta']['total']);
    self::assertNotContains(true, \array_column($beforeRead['data'], 'isRead'));

    $readId = $this->markOneCommentRead();

    $unread = $this->fetchPage('?limit=100&unread=true');
    $all = $this->fetchPage('?limit=100');

    self::assertSame(self::NOTIFICATION_COUNT - 1, $unread['meta']['total']);
    self::assertNotContains($readId, \array_column($unread['data'], 'id'));
    self::assertSame(self::NOTIFICATION_COUNT, $all['meta']['total']);
    self::assertContains($readId, \array_column($all['data'], 'id'));
  }

  /**
   * @return iterable<string, array{string, int}>
   */
  public static function unreadSpellings(): iterable {
    yield 'absent' => ['', self::NOTIFICATION_COUNT];
    yield 'false' => ['&unread=false', self::NOTIFICATION_COUNT];
    yield 'zero' => ['&unread=0', self::NOTIFICATION_COUNT];
    yield 'true' => ['&unread=true', self::NOTIFICATION_COUNT - 1];
    yield 'one' => ['&unread=1', self::NOTIFICATION_COUNT - 1];
  }

  #[Test]
  #[DataProvider('unreadSpellings')]
  public function unreadFalseAppliesNoReadFilter(string $query, int $expectedTotal): void {
    $this->seedOwnerNotifications();
    $this->markOneCommentRead();

    self::assertSame($expectedTotal, $this->fetchPage('?limit=100' . $query)['meta']['total']);
  }

  #[Test]
  public function combinesTypeAndUnread(): void {
    $this->seedOwnerNotifications();
    $readId = $this->markOneCommentRead();

    $unreadComments = $this->fetchPage('?limit=100&type=comment&unread=true');
    $unreadBookmarks = $this->fetchPage('?limit=100&type=added_to_bookmarks&unread=true');

    self::assertSame(1, $unreadComments['meta']['total']);
    self::assertCount(1, $unreadComments['data']);
    self::assertSame('comment', $unreadComments['data'][0]['type']);
    self::assertNotSame($readId, $unreadComments['data'][0]['id']);
    self::assertSame(2, $unreadBookmarks['meta']['total']);
  }

  #[Test]
  public function typeFilterIgnoresReadStateAfterMarkAllRead(): void {
    $this->seedOwnerNotifications();

    self::assertContains($this->apiRequest('POST', '/api/notifications/mark-all-read', $this->ownerToken), [200, 204]);

    $unread = $this->fetchPage('?limit=100&unread=true');
    $all = $this->fetchPage('?limit=100');

    self::assertSame(0, $unread['meta']['total']);
    self::assertSame([], $unread['data']);
    self::assertSame(3, $this->fetchPage('?limit=100&type=comment_reply')['meta']['total']);
    self::assertNotContains(false, \array_column($all['data'], 'isRead'));
  }

  #[Test]
  public function paginatesWithinTheFilteredSet(): void {
    $this->seedOwnerNotifications();

    $firstPage = $this->fetchPage('?type=added_to_bookmarks&page=1&limit=1');
    $secondPage = $this->fetchPage('?type=added_to_bookmarks&page=2&limit=1');
    $pastTheEnd = $this->fetchPage('?type=added_to_bookmarks&page=3&limit=1');

    self::assertSame(['page' => 2, 'size' => 1, 'total' => 2], $this->pagination($secondPage['meta']));
    self::assertCount(1, $secondPage['data']);
    self::assertSame('added_to_bookmarks', $secondPage['data'][0]['type']);
    self::assertNotSame($firstPage['data'][0]['id'], $secondPage['data'][0]['id']);
    self::assertSame(2, $pastTheEnd['meta']['total']);
    self::assertSame([], $pastTheEnd['data']);
  }

  #[Test]
  public function paginatesCombinedFilters(): void {
    $this->seedOwnerNotifications();

    $firstPage = $this->fetchPage('?type=comment_reply&unread=true&page=1&limit=2');
    $secondPage = $this->fetchPage('?type=comment_reply&unread=true&page=2&limit=2');
    $ids = [...\array_column($firstPage['data'], 'id'), ...\array_column($secondPage['data'], 'id')];

    self::assertSame(3, $secondPage['meta']['total']);
    self::assertCount(1, $secondPage['data']);
    self::assertCount(3, \array_unique($ids));
  }

  #[Test]
  public function noFiltersReturnEverything(): void {
    $this->seedOwnerNotifications();

    $page = $this->fetchPage('');

    self::assertSame(['page' => 1, 'size' => 20, 'total' => self::NOTIFICATION_COUNT], $this->pagination($page['meta']));
    self::assertCount(self::NOTIFICATION_COUNT, $page['data']);
  }

  /**
   * @return iterable<string, array{string}>
   */
  public static function invalidFilters(): iterable {
    yield 'unknown type' => ['?type=nope'];
    yield 'wrong case type' => ['?type=COMMENT'];
    yield 'non boolean unread' => ['?unread=maybe'];
  }

  #[Test]
  #[DataProvider('invalidFilters')]
  public function rejectsInvalidFilters(string $query): void {
    $this->createUser('filter-owner@local.test', 'filterowner', self::PASSWORD);
    $ownerToken = $this->login('filterowner', self::PASSWORD);

    self::assertSame(400, $this->apiRequest('GET', '/api/notifications' . $query, $ownerToken));
  }

  #[Test]
  public function emptyTypeReadsAsNoFilter(): void {
    $this->seedOwnerNotifications();

    $page = $this->fetchPage('?type=');

    self::assertSame(['page' => 1, 'size' => 20, 'total' => self::NOTIFICATION_COUNT], $this->pagination($page['meta']));
    self::assertCount(self::NOTIFICATION_COUNT, $page['data']);
    self::assertSame(\array_column($this->fetchPage('')['data'], 'id'), \array_column($page['data'], 'id'));
  }

  #[Test]
  public function emptyTypeStillCombinesWithUnread(): void {
    $this->seedOwnerNotifications();
    $this->markOneCommentRead();

    $emptyType = $this->fetchPage('?limit=100&type=&unread=true');

    self::assertSame(self::NOTIFICATION_COUNT - 1, $emptyType['meta']['total']);
    self::assertSame(
      \array_column($this->fetchPage('?limit=100&unread=true')['data'], 'id'),
      \array_column($emptyType['data'], 'id'),
    );
  }

  #[Test]
  public function filtersNeverLeakAnotherUsersNotifications(): void {
    $this->seedOwnerNotifications();
    $this->createUser('filter-non-owner@local.test', 'filternonowner', self::PASSWORD);
    $nonOwnerToken = $this->login('filternonowner', self::PASSWORD);

    foreach (['?type=comment', '?type=added_to_bookmarks', '?unread=true'] as $query) {
      $page = $this->fetchPage($query, $nonOwnerToken);
      self::assertSame(0, $page['meta']['total']);
      self::assertSame([], $page['data']);
    }

    self::assertSame(0, $this->fetchPage('?type=comment_reply', $this->firstReaderToken)['meta']['total']);
  }

  #[Test]
  public function anonymousIsDeniedBeforeValidation(): void {
    self::assertSame(401, $this->apiRequest('GET', '/api/notifications?type=comment&unread=true'));
    self::assertSame(401, $this->apiRequest('GET', '/api/notifications?type=nope'));
    self::assertSame(401, $this->apiRequest('GET', '/api/notifications?type='));
  }

  #[Test]
  public function unreadCountStaysUnfiltered(): void {
    $this->seedOwnerNotifications();
    $this->markOneCommentRead();

    self::assertSame(200, $this->apiRequest('GET', '/api/notifications/unread-count?type=comment', $this->ownerToken));
    self::assertSame(self::NOTIFICATION_COUNT - 1, $this->responsePayload()['count']);
  }

  private function markOneCommentRead(): string {
    $commentId = $this->fetchPage('?limit=100&type=comment')['data'][0]['id'];

    self::assertContains(
      $this->apiRequest('POST', \sprintf('/api/notifications/%s/read', $commentId), $this->ownerToken),
      [200, 204],
      'Marking a notification read failed.',
    );

    return $commentId;
  }

  /**
   * @param array{page: int, size: int, total: int} $meta
   * @return array{page: int, size: int, total: int}
   */
  private function pagination(array $meta): array {
    return ['page' => $meta['page'], 'size' => $meta['size'], 'total' => $meta['total']];
  }
}
