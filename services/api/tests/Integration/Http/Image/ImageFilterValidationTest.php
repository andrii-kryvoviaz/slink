<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Image;

use PHPUnit\Framework\Attributes\Test;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Tests\Integration\Http\HttpTestCase;

final class ImageFilterValidationTest extends HttpTestCase {
  private function storageRoot(): string {
    /** @var ConfigurationProviderInterface<object> $provider */
    $provider = static::getContainer()->get(ConfigurationProviderInterface::class);

    return (string) $provider->get('storage.adapter.local.dir') . '/slink';
  }

  private function storedOriginal(string $imageId): string {
    $files = \glob($this->storageRoot() . '/images/' . $imageId . '.*') ?: [];
    self::assertCount(1, $files, 'Expected exactly one stored original for ' . $imageId);

    return $files[0];
  }

  #[Test]
  public function itRejectsUnknownFilter(): void {
    $this->createUser('filter-owner@local.test', 'filterowner', self::PASSWORD);
    $ownerToken = $this->login('filterowner', self::PASSWORD);
    $imageId = $this->uploadImage($ownerToken, false);
    $extension = \pathinfo($this->storedOriginal($imageId), PATHINFO_EXTENSION);

    $status = $this->apiRequest('GET', \sprintf('/api/image/%s.%s?filter=blur', $imageId, $extension), $ownerToken);

    self::assertSame(404, $status, 'Unknown filter was not rejected: ' . (string) $this->client->getResponse()->getContent());
    self::assertSame('filter', $this->responsePayload()['error']['violations'][0]['property'] ?? null);
    self::assertSame([], \glob($this->storageRoot() . '/cache/' . $imageId . '-filter*') ?: []);
  }

  #[Test]
  public function itServesOwnImageWithKnownFilter(): void {
    $this->createUser('filter-owner@local.test', 'filterowner', self::PASSWORD);
    $ownerToken = $this->login('filterowner', self::PASSWORD);
    $ownerImageId = $this->uploadImage($ownerToken, false);
    $extension = \pathinfo($this->storedOriginal($ownerImageId), PATHINFO_EXTENSION);

    $status = $this->apiRequest('GET', \sprintf('/api/image/%s.%s?filter=noir', $ownerImageId, $extension), $ownerToken);

    self::assertSame(200, $status, 'Known filter request failed: ' . (string) $this->client->getResponse()->getContent());
  }
}
