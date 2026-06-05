<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Share;

use PHPUnit\Framework\Attributes\Test;
use Tests\Integration\Http\HttpTestCase;

final class ShareLifecycleTest extends HttpTestCase {
  private string $ownerToken = '';

  private function bootOwner(): void {
    $this->createUser('share-lifecycle-owner@local.test', 'sharelifecycleowner', self::PASSWORD);
    $this->ownerToken = $this->login('sharelifecycleowner', self::PASSWORD);
  }

  /**
   * @return array<int, array<string, mixed>>
   */
  private function listShares(): array {
    $status = $this->apiRequest('GET', '/api/shares', $this->ownerToken);
    self::assertSame(200, $status, 'List shares failed: ' . (string) $this->client->getResponse()->getContent());

    /** @var array{data?: array<int, array<string, mixed>>} $payload */
    $payload = \json_decode((string) $this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

    return $payload['data'] ?? [];
  }

  /**
   * @return array<string, mixed>|null
   */
  private function findShare(string $shareId): ?array {
    foreach ($this->listShares() as $share) {
      if (($share['shareId'] ?? null) === $shareId) {
        return $share;
      }
    }

    return null;
  }

  #[Test]
  public function newUnpublishedShareIsAbsentFromList(): void {
    $this->bootOwner();

    $imageId = $this->uploadImage($this->ownerToken, false);
    $shareId = $this->createImageShare($this->ownerToken, $imageId);
    self::assertNotSame('', $shareId, 'Create image share did not return a shareId.');

    self::assertNull(
      $this->findShare($shareId),
      'Unpublished share should be hidden from the owner share list.',
    );
  }

  #[Test]
  public function ownerPublishesAndUnpublishesShare(): void {
    $this->bootOwner();

    $imageId = $this->uploadImage($this->ownerToken, false);
    $shareId = $this->createImageShare($this->ownerToken, $imageId);

    $this->publishShare($this->ownerToken, $shareId);

    $publishedShare = $this->findShare($shareId);
    self::assertNotNull($publishedShare, 'Published share is missing from the owner share list.');
    self::assertTrue($publishedShare['isPublished'], 'Listed share should report isPublished=true.');

    $this->unpublishShare($this->ownerToken, $shareId);

    self::assertNull(
      $this->findShare($shareId),
      'Unpublished share should be removed from the owner share list.',
    );
  }
}
