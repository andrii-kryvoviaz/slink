<?php

declare(strict_types=1);

namespace Tests\Integration\Http\User;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\Integration\Http\HttpTestCase;

final class PurgeCommentIntegrityTest extends HttpTestCase {
  private string $adminId = '';
  private string $adminToken = '';

  private function bootAdmin(): void {
    $this->adminId = $this->createUser('admin@local.test', 'adminuser', self::PASSWORD);
    $this->grantAdmin($this->adminId);
    $this->adminToken = $this->login('adminuser', self::PASSWORD);
  }

  private function purge(string $userId): int {
    return $this->apiRequest('DELETE', '/api/user/' . $userId, $this->adminToken);
  }

  private function postComment(string $token, string $imageId, string $content, ?string $referencedCommentId = null): string {
    $body = ['content' => $content];

    if ($referencedCommentId !== null) {
      $body['referencedCommentId'] = $referencedCommentId;
    }

    $status = $this->apiRequest(
      'POST',
      \sprintf('/api/image/%s/comments', $imageId),
      $token,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode($body, JSON_THROW_ON_ERROR),
    );

    self::assertSame(201, $status, 'Post comment failed: ' . (string) $this->client->getResponse()->getContent());

    return $this->extractId((string) $this->client->getResponse()->getContent());
  }

  /**
   * @return array<int, array<string, mixed>>
   */
  private function fetchComments(string $imageId, string $token): array {
    $status = $this->apiRequest('GET', \sprintf('/api/image/%s/comments', $imageId), $token);
    self::assertSame(200, $status, 'Fetch comments failed: ' . (string) $this->client->getResponse()->getContent());

    /** @var array{data: array<int, array<string, mixed>>} $payload */
    $payload = \json_decode((string) $this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

    return $payload['data'];
  }

  private function countRowsByColumn(string $table, string $column, string $value): int {
    /** @var EntityManagerInterface $entityManager */
    $entityManager = static::getContainer()->get(EntityManagerInterface::class);

    return (int) $entityManager->getConnection()->fetchOne(
      \sprintf('SELECT COUNT(*) FROM "%s" WHERE %s = :value', $table, $column),
      ['value' => $value],
    );
  }

  #[Test]
  public function referencedCommentAuthorIsNullAfterPurgeButTheReplyAndOriginalTextRemain(): void {
    $this->setAccessSettings([]);
    $this->bootAdmin();
    $memberId = $this->createUser('member@local.test', 'memberuser', self::PASSWORD);
    $nonOwnerId = $this->createUser('nonowner@local.test', 'nonowneruser', self::PASSWORD);
    $memberToken = $this->login('memberuser', self::PASSWORD);
    $nonOwnerToken = $this->login('nonowneruser', self::PASSWORD);

    $adminImage = $this->uploadImage($this->adminToken, true);
    $rootComment = $this->postComment($memberToken, $adminImage, 'member root comment');
    $replyComment = $this->postComment($nonOwnerToken, $adminImage, 'nonOwner reply', $rootComment);

    self::assertSame(204, $this->purge($memberId), 'Purge failed: ' . (string) $this->client->getResponse()->getContent());

    $comments = $this->fetchComments($adminImage, $this->adminToken);
    $replies = \array_values(\array_filter(
      $comments,
      static fn(array $comment): bool => ($comment['id'] ?? null) === $replyComment,
    ));

    self::assertCount(1, $replies, 'Expected to find the reply comment.');
    self::assertSame($nonOwnerId, $replies[0]['author']['id'] ?? null, "Expected the reply's own author to still resolve.");

    $referencedComment = $replies[0]['referencedComment'] ?? null;
    self::assertIsArray($referencedComment);
    self::assertArrayHasKey('author', $referencedComment);
    self::assertNull($referencedComment['author']);
    self::assertFalse($referencedComment['isDeleted'] ?? true, 'The referenced content was not deleted, only its author was purged.');
    self::assertSame('member root comment', $referencedComment['displayContent'] ?? null);
  }

  #[Test]
  public function commentOnAPurgedUsersImageIsOrphanedButUnreachableRatherThanErroring(): void {
    $this->setAccessSettings([]);
    $this->bootAdmin();
    $memberId = $this->createUser('member@local.test', 'memberuser', self::PASSWORD);
    $this->createUser('nonowner@local.test', 'nonowneruser', self::PASSWORD);
    $memberToken = $this->login('memberuser', self::PASSWORD);
    $nonOwnerToken = $this->login('nonowneruser', self::PASSWORD);

    $memberImage = $this->uploadImage($memberToken, true);
    $this->postComment($nonOwnerToken, $memberImage, 'nonOwner comment on member image');

    self::assertSame(1, $this->countRowsByColumn('comment', 'image_id', $memberImage));

    self::assertSame(204, $this->purge($memberId), 'Purge failed: ' . (string) $this->client->getResponse()->getContent());

    self::assertSame(
      1,
      $this->countRowsByColumn('comment', 'image_id', $memberImage),
      'Expected the orphaned comment row to survive; nothing sweeps comments when the image is force-deleted by a purge.',
    );

    self::assertSame(404, $this->apiRequest('GET', \sprintf('/api/image/%s/comments', $memberImage), $nonOwnerToken));
  }
}
