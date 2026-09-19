<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Notification;

use PHPUnit\Framework\Attributes\Test;
use Tests\Integration\Http\HttpTestCase;

final class GetNotificationsPaginationTest extends HttpTestCase {
  private const int NOTIFICATION_COUNT = 7;

  private string $ownerToken = '';

  private function seedOwnerNotifications(): void {
    $this->createUser('notifications-owner@local.test', 'notificationsowner', self::PASSWORD);
    $this->ownerToken = $this->login('notificationsowner', self::PASSWORD);
    $imageId = $this->uploadImage($this->ownerToken, true);

    for ($i = 1; $i <= self::NOTIFICATION_COUNT; $i++) {
      $this->createUser(\sprintf('reader-%02d@local.test', $i), \sprintf('reader%02d', $i), self::PASSWORD);
      $readerToken = $this->login(\sprintf('reader%02d', $i), self::PASSWORD);

      self::assertContains(
        $this->apiRequest('POST', \sprintf('/api/image/%s/bookmark', $imageId), $readerToken),
        [200, 201, 204],
        \sprintf('Reader %d failed to bookmark.', $i),
      );
    }

    self::assertSame(self::NOTIFICATION_COUNT, $this->fetchPage('?limit=100')['meta']['total'], 'Seeding must produce one notification per reader.');
  }

  /**
   * @return array{meta: array{page: int, size: int, total: int}, ids: list<string>}
   */
  private function fetchPage(string $query, ?string $token = null): array {
    $status = $this->apiRequest('GET', '/api/notifications' . $query, $token ?? $this->ownerToken);
    self::assertSame(200, $status, 'Fetch notifications failed: ' . (string) $this->client->getResponse()->getContent());

    /** @var array{meta: array{page: int, size: int, total: int}, data: list<array{id: string}>} $payload */
    $payload = $this->responsePayload();

    return [
      'meta' => $payload['meta'],
      'ids' => \array_column($payload['data'], 'id'),
    ];
  }

  #[Test]
  public function servesRequestedPageAndLimit(): void {
    $this->seedOwnerNotifications();

    $firstPage = $this->fetchPage('?page=1&limit=5');
    $secondPage = $this->fetchPage('?page=2&limit=5');

    self::assertSame(['page' => 1, 'size' => 5, 'total' => 7], $this->pagination($firstPage['meta']));
    self::assertCount(5, $firstPage['ids']);
    self::assertSame(['page' => 2, 'size' => 5, 'total' => 7], $this->pagination($secondPage['meta']));
    self::assertCount(2, $secondPage['ids']);
    self::assertSame([], \array_intersect($firstPage['ids'], $secondPage['ids']));
    self::assertCount(self::NOTIFICATION_COUNT, \array_unique([...$firstPage['ids'], ...$secondPage['ids']]));
  }

  #[Test]
  public function defaultsToFirstPageOfTwenty(): void {
    $this->seedOwnerNotifications();

    $page = $this->fetchPage('');

    self::assertSame(['page' => 1, 'size' => 20, 'total' => 7], $this->pagination($page['meta']));
    self::assertCount(self::NOTIFICATION_COUNT, $page['ids']);
  }

  #[Test]
  public function answersEmptyPagePastTheEnd(): void {
    $this->seedOwnerNotifications();

    $page = $this->fetchPage('?page=3&limit=5');

    self::assertSame(['page' => 3, 'size' => 5, 'total' => 7], $this->pagination($page['meta']));
    self::assertSame([], $page['ids']);
  }

  #[Test]
  public function clampsOutOfRangePageAndLimit(): void {
    $this->seedOwnerNotifications();

    $firstPage = $this->fetchPage('?page=1&limit=5');
    $zeroPage = $this->fetchPage('?page=0&limit=5');
    $oversized = $this->fetchPage('?limit=500');
    $zeroLimit = $this->fetchPage('?limit=0');
    $negative = $this->fetchPage('?page=-1&limit=-5');

    self::assertSame(1, $zeroPage['meta']['page']);
    self::assertSame($firstPage['ids'], $zeroPage['ids']);
    self::assertSame(100, $oversized['meta']['size']);
    self::assertCount(self::NOTIFICATION_COUNT, $oversized['ids']);
    self::assertSame(1, $zeroLimit['meta']['size']);
    self::assertCount(1, $zeroLimit['ids']);
    self::assertSame(['page' => 1, 'size' => 1, 'total' => 7], $this->pagination($negative['meta']));
  }

  #[Test]
  public function nonOwnerPagesOnlyOwnNotifications(): void {
    $this->seedOwnerNotifications();
    $this->createUser('nonowner@local.test', 'nonowneruser', self::PASSWORD);
    $nonOwnerToken = $this->login('nonowneruser', self::PASSWORD);

    $page = $this->fetchPage('?page=1&limit=5', $nonOwnerToken);

    self::assertSame(0, $page['meta']['total']);
    self::assertSame([], $page['ids']);
  }

  #[Test]
  public function anonymousIsDenied(): void {
    self::assertSame(401, $this->apiRequest('GET', '/api/notifications?page=2'));
  }

  /**
   * @param array{page: int, size: int, total: int} $meta
   * @return array{page: int, size: int, total: int}
   */
  private function pagination(array $meta): array {
    return ['page' => $meta['page'], 'size' => $meta['size'], 'total' => $meta['total']];
  }
}
