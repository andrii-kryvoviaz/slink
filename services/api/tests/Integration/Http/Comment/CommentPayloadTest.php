<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Comment;

use PHPUnit\Framework\Attributes\Test;
use Tests\Integration\Http\HttpTestCase;

final class CommentPayloadTest extends HttpTestCase {
  /**
   * @return array<int, array<string, mixed>>
   */
  protected function fetchComments(string $imageId, ?string $token = null): array {
    $status = $this->apiRequest('GET', \sprintf('/api/image/%s/comments', $imageId), $token);
    self::assertSame(200, $status, 'Fetch comments failed: ' . (string) $this->client->getResponse()->getContent());

    /** @var array{data: array<int, array<string, mixed>>} $payload */
    $payload = \json_decode((string) $this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

    return $payload['data'];
  }

  #[Test]
  public function commentsExposeIdentityFreeEditableWithoutCanEdit(): void {
    $this->setAccessSettings([]);
    $this->createUser('owner@local.test', 'owneruser', self::PASSWORD);
    $this->createUser('shareviewer@local.test', 'shareviewer', self::PASSWORD);
    $ownerToken = $this->login('owneruser', self::PASSWORD);
    $shareViewerToken = $this->login('shareviewer', self::PASSWORD);

    $imageId = $this->uploadImage($ownerToken, false);
    $collectionId = $this->createCollection($ownerToken);
    $this->addImageToCollection($ownerToken, $collectionId, $imageId);
    $shareId = $this->createCollectionShare($ownerToken, $collectionId);
    $this->publishShare($ownerToken, $shareId);

    self::assertSame(201, $this->apiRequest(
      'POST',
      \sprintf('/api/image/%s/comments', $imageId),
      $ownerToken,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['content' => 'payload comment'], JSON_THROW_ON_ERROR),
    ));

    $ownerComments = $this->fetchComments($imageId, $ownerToken);

    self::assertCount(1, $ownerComments);
    $comment = $ownerComments[0];

    self::assertArrayNotHasKey('canEdit', $comment);
    self::assertArrayHasKey('editable', $comment);
    self::assertIsArray($comment['editable']);
    self::assertArrayHasKey('formattedDate', $comment['editable']);
    self::assertArrayHasKey('timestamp', $comment['editable']);
    self::assertIsInt($comment['editable']['timestamp']);

    $shareViewerComments = $this->fetchComments($imageId, $shareViewerToken);

    self::assertSame($ownerComments, $shareViewerComments);
  }
}
