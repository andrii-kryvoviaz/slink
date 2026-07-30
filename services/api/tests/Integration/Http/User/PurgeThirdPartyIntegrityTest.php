<?php

declare(strict_types=1);

namespace Tests\Integration\Http\User;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\Integration\Http\HttpTestCase;

final class PurgeThirdPartyIntegrityTest extends HttpTestCase {
  private string $adminToken = '';

  private function purge(string $userId): int {
    return $this->apiRequest('DELETE', '/api/user/' . $userId, $this->adminToken);
  }

  /**
   * @return array<int, array<string, mixed>>
   */
  private function fetchNotifications(string $token): array {
    $status = $this->apiRequest('GET', '/api/notifications', $token);
    self::assertSame(200, $status, 'Fetch notifications failed: ' . (string) $this->client->getResponse()->getContent());

    /** @var array{data: array<int, array<string, mixed>>} $payload */
    $payload = \json_decode((string) $this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

    return $payload['data'];
  }

  /**
   * @return array<int, array<string, mixed>>
   */
  private function fetchCollectionItems(string $collectionId, string $token): array {
    $status = $this->apiRequest('GET', \sprintf('/api/collection/%s/items', $collectionId), $token);
    self::assertSame(200, $status, 'Fetch collection items failed: ' . (string) $this->client->getResponse()->getContent());

    /** @var array{data: array<int, array<string, mixed>>} $payload */
    $payload = \json_decode((string) $this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

    return $payload['data'];
  }

  private function collectionItemsTotal(string $collectionId, string $token): int {
    $status = $this->apiRequest('GET', \sprintf('/api/collection/%s/items', $collectionId), $token);
    self::assertSame(200, $status, 'Fetch collection items failed: ' . (string) $this->client->getResponse()->getContent());

    /** @var array{meta: array{total: int}} $payload */
    $payload = \json_decode((string) $this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

    return $payload['meta']['total'];
  }

  private function countNotificationsForUserReferencingImage(string $userId, string $imageId): int {
    /** @var EntityManagerInterface $entityManager */
    $entityManager = static::getContainer()->get(EntityManagerInterface::class);

    return (int) $entityManager->getConnection()->fetchOne(
      'SELECT COUNT(*) FROM "notification" WHERE user_id = :userId AND reference_id = :imageId',
      ['userId' => $userId, 'imageId' => $imageId],
    );
  }

  #[Test]
  public function nonOwnersReplyNotificationIsRemovedWhenPurgeDeletesTheRepliedToImage(): void {
    $this->setAccessSettings([]);
    $this->adminToken = $this->bootAdmin();
    $memberId = $this->createUser('member@local.test', 'memberuser', self::PASSWORD);
    $nonOwnerId = $this->createUser('nonowner@local.test', 'nonowneruser', self::PASSWORD);
    $memberToken = $this->login('memberuser', self::PASSWORD);
    $nonOwnerToken = $this->login('nonowneruser', self::PASSWORD);

    $memberImage = $this->uploadImage($memberToken, true);
    $rootComment = $this->postComment($nonOwnerToken, $memberImage, 'nonOwner root comment');
    $this->postComment($memberToken, $memberImage, 'member reply', $rootComment);

    self::assertSame(1, $this->countNotificationsForUserReferencingImage($nonOwnerId, $memberImage));

    $before = $this->fetchNotifications($nonOwnerToken);
    $replyNotifications = \array_values(\array_filter(
      $before,
      static fn(array $notification): bool => ($notification['type'] ?? null) === 'comment_reply',
    ));

    self::assertCount(1, $replyNotifications, 'Expected exactly one reply notification before purge.');
    self::assertSame($memberId, $replyNotifications[0]['actor']['id'] ?? null);
    self::assertSame($memberImage, $replyNotifications[0]['reference']['id'] ?? null);

    self::assertSame(204, $this->purge($memberId), 'Purge failed: ' . (string) $this->client->getResponse()->getContent());

    self::assertSame(
      0,
      $this->countNotificationsForUserReferencingImage($nonOwnerId, $memberImage),
      'Expected the reply notification row to be removed along with the image it references.',
    );

    $after = $this->fetchNotifications($nonOwnerToken);
    self::assertSame(
      [],
      \array_values(\array_filter(
        $after,
        static fn(array $notification): bool => ($notification['reference']['id'] ?? null) === $memberImage,
      )),
    );
  }

  #[Test]
  public function nonOwnersCollectionContainingAPurgedUsersImageStaysUsable(): void {
    $this->setAccessSettings([]);
    $this->adminToken = $this->bootAdmin();
    $memberId = $this->createUser('member@local.test', 'memberuser', self::PASSWORD);
    $this->createUser('nonowner@local.test', 'nonowneruser', self::PASSWORD);
    $memberToken = $this->login('memberuser', self::PASSWORD);
    $nonOwnerToken = $this->login('nonowneruser', self::PASSWORD);

    $memberImage = $this->uploadImage($memberToken, true);
    $collectionId = $this->createCollection($nonOwnerToken);

    $addStatus = $this->apiRequest('POST', \sprintf('/api/collection/%s/items/%s', $collectionId, $memberImage), $nonOwnerToken);
    self::assertSame(201, $addStatus, 'Add item to collection failed: ' . (string) $this->client->getResponse()->getContent());

    self::assertCount(1, $this->fetchCollectionItems($collectionId, $nonOwnerToken));

    self::assertSame(204, $this->purge($memberId), 'Purge failed: ' . (string) $this->client->getResponse()->getContent());

    $collectionStatus = $this->apiRequest('GET', \sprintf('/api/collection/%s', $collectionId), $nonOwnerToken);
    self::assertSame(200, $collectionStatus, 'Collection lookup broke after purge: ' . (string) $this->client->getResponse()->getContent());

    $items = $this->fetchCollectionItems($collectionId, $nonOwnerToken);
    $itemIds = \array_column($items, 'itemId');
    self::assertNotContains($memberImage, $itemIds);

    $total = $this->collectionItemsTotal($collectionId, $nonOwnerToken);
    self::assertSame(
      \count($items),
      $total,
      \sprintf('List (%d items) and reported total (%d) disagree after purge.', \count($items), $total),
    );
  }
}
