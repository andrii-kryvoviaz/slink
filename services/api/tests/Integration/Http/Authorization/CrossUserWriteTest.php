<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Authorization;

use PHPUnit\Framework\Attributes\Test;
use Tests\Integration\Http\HttpTestCase;

final class CrossUserWriteTest extends HttpTestCase {
  private string $ownerToken = '';
  private string $otherToken = '';
  private string $adminToken = '';

  private function bootActors(): void {
    $this->createUser('writer-owner@local.test', 'writerowner', self::PASSWORD);
    $this->createUser('writer-other@local.test', 'writerother', self::PASSWORD);
    $adminId = $this->createUser('writer-admin@local.test', 'writeradmin', self::PASSWORD);
    $this->grantAdmin($adminId);

    $this->ownerToken = $this->login('writerowner', self::PASSWORD);
    $this->otherToken = $this->login('writerother', self::PASSWORD);
    $this->adminToken = $this->login('writeradmin', self::PASSWORD);
  }

  private function createComment(string $token, string $imageId): string {
    $this->client->request(
      'POST',
      \sprintf('/api/image/%s/comments', $imageId),
      [],
      [],
      ['HTTP_AUTHORIZATION' => 'Bearer ' . $token, 'CONTENT_TYPE' => 'application/json'],
      \json_encode(['content' => 'owner comment'], JSON_THROW_ON_ERROR),
    );

    $response = $this->client->getResponse();
    self::assertSame(201, $response->getStatusCode(), 'Create comment failed: ' . (string) $response->getContent());

    return $this->extractId((string) $response->getContent());
  }

  private function patchImage(string $token, string $imageId): int {
    return $this->apiRequest(
      'PATCH',
      \sprintf('/api/image/%s', $imageId),
      $token,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['description' => 'hijacked', 'isPublic' => true], JSON_THROW_ON_ERROR),
    );
  }

  private function patchComment(string $token, string $commentId): int {
    return $this->apiRequest(
      'PATCH',
      \sprintf('/api/comment/%s', $commentId),
      $token,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['content' => 'hijacked comment'], JSON_THROW_ON_ERROR),
    );
  }

  #[Test]
  public function nonOwnerCannotDeleteImage(): void {
    $this->bootActors();
    $image = $this->uploadImage($this->ownerToken, true);

    self::assertSame(403, $this->apiRequest('DELETE', \sprintf('/api/image/%s', $image), $this->otherToken));
  }

  #[Test]
  public function nonOwnerCannotUpdateImage(): void {
    $this->bootActors();
    $image = $this->uploadImage($this->ownerToken, true);

    self::assertSame(403, $this->patchImage($this->otherToken, $image));
  }

  #[Test]
  public function nonOwnerCannotAddItemToCollection(): void {
    $this->bootActors();
    $collection = $this->createCollection($this->ownerToken);
    $image = $this->uploadImage($this->otherToken, false);

    self::assertSame(400, $this->apiRequest(
      'POST',
      \sprintf('/api/collection/%s/items/%s', $collection, $image),
      $this->otherToken,
    ));
  }

  #[Test]
  public function nonOwnerCannotEditOrDeleteComment(): void {
    $this->bootActors();
    $image = $this->uploadImage($this->ownerToken, true);
    $comment = $this->createComment($this->ownerToken, $image);

    self::assertSame(403, $this->patchComment($this->otherToken, $comment));
    self::assertSame(403, $this->apiRequest('DELETE', \sprintf('/api/comment/%s', $comment), $this->otherToken));
  }

  #[Test]
  public function nonAdminCannotUseAdminImageDelete(): void {
    $this->bootActors();
    $image = $this->uploadImage($this->ownerToken, true);

    self::assertSame(403, $this->apiRequest(
      'DELETE',
      \sprintf('/api/admin/image/%s', $image),
      $this->otherToken,
      ['CONTENT_TYPE' => 'application/json'],
      '{}',
    ));
  }

  #[Test]
  public function adminCanDeleteAnyImageViaAdminRoute(): void {
    $this->bootActors();
    $image = $this->uploadImage($this->ownerToken, true);

    self::assertContains($this->apiRequest(
      'DELETE',
      \sprintf('/api/admin/image/%s', $image),
      $this->adminToken,
      ['CONTENT_TYPE' => 'application/json'],
      '{}',
    ), [200, 204]);
  }
}
