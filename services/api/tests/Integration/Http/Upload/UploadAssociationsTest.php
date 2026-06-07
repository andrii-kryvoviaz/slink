<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Upload;

use PHPUnit\Framework\Attributes\Test;
use Tests\Integration\Http\HttpTestCase;

final class UploadAssociationsTest extends HttpTestCase {
  private string $ownerToken = '';

  private function bootOwner(): void {
    $this->createUser('assoc-owner@local.test', 'assocowner', self::PASSWORD);
    $this->ownerToken = $this->login('assocowner', self::PASSWORD);
  }

  private function createTag(string $name): string {
    $this->client->request(
      'POST',
      '/api/tags',
      [],
      [],
      ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->ownerToken, 'CONTENT_TYPE' => 'application/json'],
      \json_encode(['name' => $name], JSON_THROW_ON_ERROR),
    );

    $response = $this->client->getResponse();
    self::assertContains($response->getStatusCode(), [200, 201], 'Create tag failed: ' . (string) $response->getContent());

    return $this->extractId((string) $response->getContent());
  }

  private function tagImageCount(string $tagId): int {
    $this->apiRequest('GET', \sprintf('/api/tags/%s', $tagId), $this->ownerToken);

    /** @var array{data?: array{imageCount?: int}} $payload */
    $payload = \json_decode((string) $this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

    return $payload['data']['imageCount'] ?? 0;
  }

  /**
   * @return array<int, array<string, mixed>>
   */
  private function listCollectionItems(string $collectionId): array {
    $this->apiRequest('GET', \sprintf('/api/collection/%s/items', $collectionId), $this->ownerToken);

    /** @var array{data?: array<int, array<string, mixed>>} $payload */
    $payload = \json_decode((string) $this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

    return $payload['data'] ?? [];
  }

  #[Test]
  public function authenticatedUploadTagsImageAndAddsToCollectionWithinSameRequest(): void {
    $this->bootOwner();

    $tagId = $this->createTag('association-tag');
    $collectionId = $this->createCollection($this->ownerToken);

    $this->client->request(
      'POST',
      '/api/upload',
      ['tagIds' => [$tagId], 'collectionIds' => [$collectionId]],
      ['image' => $this->sampleImage()],
      ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->ownerToken],
    );

    $response = $this->client->getResponse();
    self::assertSame(201, $response->getStatusCode(), 'Upload failed: ' . (string) $response->getContent());

    $imageId = $this->extractId((string) $response->getContent());
    self::assertNotSame('', $imageId);

    self::assertSame(1, $this->tagImageCount($tagId), 'Uploaded image was not tagged.');

    $itemIds = \array_map(static fn(array $item): mixed => $item['itemId'] ?? null, $this->listCollectionItems($collectionId));
    self::assertContains($imageId, $itemIds, 'Uploaded image was not added to the collection.');
  }

  #[Test]
  public function authenticatedUploadWithoutAssociationsLeavesTagCountUnchanged(): void {
    $this->bootOwner();

    $tagId = $this->createTag('unused-tag');

    $this->client->request(
      'POST',
      '/api/upload',
      [],
      ['image' => $this->sampleImage()],
      ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->ownerToken],
    );

    self::assertSame(201, $this->client->getResponse()->getStatusCode());
    self::assertSame(0, $this->tagImageCount($tagId));
  }
}
