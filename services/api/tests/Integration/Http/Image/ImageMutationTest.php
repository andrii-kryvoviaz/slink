<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Image;

use PHPUnit\Framework\Attributes\Test;
use Tests\Integration\Http\HttpTestCase;

final class ImageMutationTest extends HttpTestCase {
  private string $ownerToken = '';

  private function bootOwner(): void {
    $this->createUser('mutation-owner@local.test', 'mutationowner', self::PASSWORD);
    $this->ownerToken = $this->login('mutationowner', self::PASSWORD);
  }

  /**
   * @return array<string, mixed>
   */
  private function getImageDetail(string $token, string $imageId): array {
    $status = $this->apiRequest('GET', \sprintf('/api/image/%s/detail', $imageId), $token);
    self::assertSame(200, $status, 'Detail request failed: ' . (string) $this->client->getResponse()->getContent());

    /** @var array{data?: array<string, mixed>} $payload */
    $payload = \json_decode((string) $this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

    return $payload['data'] ?? [];
  }

  #[Test]
  public function ownerCanUpdateVisibilityAndDescription(): void {
    $this->bootOwner();

    $imageId = $this->uploadImage($this->ownerToken, false);

    $status = $this->apiRequest(
      'PATCH',
      \sprintf('/api/image/%s', $imageId),
      $this->ownerToken,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['description' => 'updated caption', 'isPublic' => true], JSON_THROW_ON_ERROR),
    );

    self::assertContains($status, [200, 204], 'Update failed: ' . (string) $this->client->getResponse()->getContent());

    $detail = $this->getImageDetail($this->ownerToken, $imageId);

    self::assertTrue($detail['isPublic'] ?? null);
    self::assertSame('updated caption', $detail['description'] ?? null);
  }

  #[Test]
  public function ownerCanDeleteImageThenDetailReturnsNotFound(): void {
    $this->bootOwner();

    $imageId = $this->uploadImage($this->ownerToken, true);

    $status = $this->apiRequest('DELETE', \sprintf('/api/image/%s', $imageId), $this->ownerToken);
    self::assertContains($status, [200, 204], 'Delete failed: ' . (string) $this->client->getResponse()->getContent());

    self::assertSame(404, $this->apiRequest('GET', \sprintf('/api/image/%s/detail', $imageId), $this->ownerToken));
  }
}
