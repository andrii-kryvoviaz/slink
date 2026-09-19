<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Notification;

use PHPUnit\Framework\Attributes\Test;
use Tests\Integration\Http\HttpTestCase;

final class GetNotificationsParentCommentTest extends HttpTestCase {
  private string $ownerToken = '';

  private string $nonOwnerToken = '';

  private string $imageId = '';

  private function seedActors(): void {
    $this->createUser('parent-owner@local.test', 'parentowner', self::PASSWORD);
    $this->createUser('parent-nonowner@local.test', 'parentnonowner', self::PASSWORD);
    $this->ownerToken = $this->login('parentowner', self::PASSWORD);
    $this->nonOwnerToken = $this->login('parentnonowner', self::PASSWORD);
    $this->imageId = $this->uploadImage($this->ownerToken, true);
  }

  /**
   * @return array<string, mixed>
   */
  private function fetchItem(string $type, string $relatedCommentId): array {
    foreach ($this->fetchNotifications() as $item) {
      if ($item['type'] === $type && ($item['relatedComment']['id'] ?? null) === $relatedCommentId) {
        return $item;
      }
    }

    self::fail(\sprintf('No %s notification for comment %s.', $type, $relatedCommentId));
  }

  /**
   * @return list<array<string, mixed>>
   */
  private function fetchNotifications(string $query = '?limit=100'): array {
    $status = $this->apiRequest('GET', '/api/notifications' . $query, $this->ownerToken);
    self::assertSame(200, $status, 'Fetch notifications failed: ' . (string) $this->client->getResponse()->getContent());

    /** @var array{data: list<array<string, mixed>>} $payload */
    $payload = $this->responsePayload();

    return $payload['data'];
  }

  #[Test]
  public function replyNotificationCarriesItsParentComment(): void {
    $this->seedActors();
    $parentId = $this->postComment($this->ownerToken, $this->imageId, 'owner original words');
    $replyId = $this->postComment($this->nonOwnerToken, $this->imageId, 'reply text', $parentId);

    $item = $this->fetchItem('comment_reply', $replyId);

    self::assertSame(['id' => $parentId, 'content' => 'owner original words'], $item['relatedComment']['referencedComment']);
  }

  #[Test]
  public function plainCommentNotificationHasNoParent(): void {
    $this->seedActors();
    $commentId = $this->postComment($this->nonOwnerToken, $this->imageId, 'plain words');

    $item = $this->fetchItem('comment', $commentId);

    self::assertArrayHasKey('referencedComment', $item['relatedComment']);
    self::assertNull($item['relatedComment']['referencedComment']);
  }

  #[Test]
  public function deletedParentIsNotExposed(): void {
    $this->seedActors();
    $parentId = $this->postComment($this->ownerToken, $this->imageId, 'owner original words');
    $replyId = $this->postComment($this->nonOwnerToken, $this->imageId, 'reply text', $parentId);

    self::assertContains($this->apiRequest('DELETE', '/api/comment/' . $parentId, $this->ownerToken), [200, 202, 204]);

    $item = $this->fetchItem('comment_reply', $replyId);

    self::assertNull($item['relatedComment']['referencedComment']);
  }

  #[Test]
  public function eachReplyCarriesItsOwnParent(): void {
    $this->seedActors();
    $firstParentId = $this->postComment($this->ownerToken, $this->imageId, 'first parent');
    $secondParentId = $this->postComment($this->ownerToken, $this->imageId, 'second parent');
    $firstReplyId = $this->postComment($this->nonOwnerToken, $this->imageId, 'first reply', $firstParentId);
    $secondReplyId = $this->postComment($this->nonOwnerToken, $this->imageId, 'second reply', $secondParentId);

    self::assertSame($firstParentId, $this->fetchItem('comment_reply', $firstReplyId)['relatedComment']['referencedComment']['id']);
    self::assertSame($secondParentId, $this->fetchItem('comment_reply', $secondReplyId)['relatedComment']['referencedComment']['id']);
  }

  #[Test]
  public function pagedReplyStillCarriesItsParent(): void {
    $this->seedActors();
    $parentId = $this->postComment($this->ownerToken, $this->imageId, 'owner original words');

    for ($i = 1; $i <= 3; $i++) {
      $this->postComment($this->nonOwnerToken, $this->imageId, 'reply ' . $i, $parentId);
    }

    $page = $this->fetchNotifications('?page=2&limit=1');

    self::assertCount(1, $page);
    self::assertSame(['id' => $parentId, 'content' => 'owner original words'], $page[0]['relatedComment']['referencedComment']);
  }

  #[Test]
  public function bookmarkNotificationHasNoRelatedComment(): void {
    $this->seedActors();

    self::assertContains($this->apiRequest('POST', \sprintf('/api/image/%s/bookmark', $this->imageId), $this->nonOwnerToken), [200, 201, 204]);

    $items = $this->fetchNotifications();

    self::assertCount(1, $items);
    self::assertNull($items[0]['relatedComment']);
  }
}
