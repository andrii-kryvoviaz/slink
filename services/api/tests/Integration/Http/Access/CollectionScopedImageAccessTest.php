<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Access;

use PHPUnit\Framework\Attributes\Test;
use Tests\Integration\Http\HttpTestCase;

final class CollectionScopedImageAccessTest extends HttpTestCase {
  private string $ownerToken = '';
  private string $otherToken = '';

  private function bootActors(): void {
    $this->createUser('owner@local.test', 'owneruser', self::PASSWORD);
    $this->createUser('viewer@local.test', 'vieweruser', self::PASSWORD);

    $this->ownerToken = $this->login('owneruser', self::PASSWORD);
    $this->otherToken = $this->login('vieweruser', self::PASSWORD);
  }

  /**
   * @return array{0: string, 1: string}
   */
  private function sharedCollectionWithImage(): array {
    $collection = $this->createCollection($this->ownerToken);
    $image = $this->uploadImage($this->ownerToken, false);
    $this->addImageToCollection($this->ownerToken, $collection, $image);
    $share = $this->createCollectionShare($this->ownerToken, $collection);
    $this->publishShare($this->ownerToken, $share);

    return [$collection, $image];
  }

  private function url(string $collectionId, string $imageId): string {
    return \sprintf('/api/image/collection/%s/items/%s.png', $collectionId, $imageId);
  }

  #[Test]
  public function ownerReadsScopedImageRegardlessOfCollectionShareFlag(): void {
    $this->setAccessSettings(['requireAuthForCollectionShares' => false]);
    $this->bootActors();
    [$collection, $image] = $this->sharedCollectionWithImage();

    self::assertSame(200, $this->apiRequest('GET', $this->url($collection, $image), $this->ownerToken));

    $this->setAccessSettings(['requireAuthForCollectionShares' => true]);
    self::assertSame(200, $this->apiRequest('GET', $this->url($collection, $image), $this->ownerToken));
  }

  #[Test]
  public function authenticatedNonOwnerReadsSharedScopedImageRegardlessOfFlag(): void {
    $this->setAccessSettings(['requireAuthForCollectionShares' => false]);
    $this->bootActors();
    [$collection, $image] = $this->sharedCollectionWithImage();

    self::assertSame(200, $this->apiRequest('GET', $this->url($collection, $image), $this->otherToken));

    $this->setAccessSettings(['requireAuthForCollectionShares' => true]);
    self::assertSame(200, $this->apiRequest('GET', $this->url($collection, $image), $this->otherToken));
  }

  #[Test]
  public function anonymousReadsSharedScopedImageWhenFlagOff(): void {
    $this->setAccessSettings(['requireAuthForCollectionShares' => false]);
    $this->bootActors();
    [$collection, $image] = $this->sharedCollectionWithImage();

    self::assertSame(200, $this->apiRequest('GET', $this->url($collection, $image)));
  }

  #[Test]
  public function anonymousIsHiddenFromSharedScopedImageWhenFlagOn(): void {
    $this->setAccessSettings(['requireAuthForCollectionShares' => true]);
    $this->bootActors();
    [$collection, $image] = $this->sharedCollectionWithImage();

    self::assertSame(404, $this->apiRequest('GET', $this->url($collection, $image)));
  }

  #[Test]
  public function anonymousIsLockedOutOfPasswordProtectedCollectionShare(): void {
    $this->setAccessSettings(['requireAuthForCollectionShares' => false]);
    $this->bootActors();

    $collection = $this->createCollection($this->ownerToken);
    $image = $this->uploadImage($this->ownerToken, false);
    $this->addImageToCollection($this->ownerToken, $collection, $image);
    $share = $this->createCollectionShare($this->ownerToken, $collection);
    $this->setSharePassword($this->ownerToken, $share, self::SHARE_PASSWORD);
    $this->publishShare($this->ownerToken, $share);

    self::assertSame(423, $this->apiRequest('GET', $this->url($collection, $image)));
  }

  #[Test]
  public function anonymousReadsPasswordProtectedCollectionShareAfterUnlock(): void {
    $this->setAccessSettings(['requireAuthForCollectionShares' => false]);
    $this->bootActors();

    $collection = $this->createCollection($this->ownerToken);
    $image = $this->uploadImage($this->ownerToken, false);
    $this->addImageToCollection($this->ownerToken, $collection, $image);
    $share = $this->createCollectionShare($this->ownerToken, $collection);
    $this->setSharePassword($this->ownerToken, $share, self::SHARE_PASSWORD);
    $this->publishShare($this->ownerToken, $share);

    [, $cookies] = $this->unlockShare($share, self::SHARE_PASSWORD);

    self::assertSame(200, $this->requestWithCookies('GET', $this->url($collection, $image), $cookies));
  }

  #[Test]
  public function anonymousGetsGoneForExpiredCollectionShare(): void {
    $this->setAccessSettings(['requireAuthForCollectionShares' => false]);
    $this->bootActors();

    $collection = $this->createCollection($this->ownerToken);
    $image = $this->uploadImage($this->ownerToken, false);
    $this->addImageToCollection($this->ownerToken, $collection, $image);
    $share = $this->createCollectionShare($this->ownerToken, $collection);
    $this->publishShare($this->ownerToken, $share);
    $this->setShareExpiration($this->ownerToken, $share, $this->futureIso(1));

    \sleep(2);

    self::assertSame(410, $this->apiRequest('GET', $this->url($collection, $image)));
  }

  #[Test]
  public function anonymousCannotReadUnpublishedCollectionShare(): void {
    $this->setAccessSettings(['requireAuthForCollectionShares' => false]);
    $this->bootActors();

    $collection = $this->createCollection($this->ownerToken);
    $image = $this->uploadImage($this->ownerToken, false);
    $this->addImageToCollection($this->ownerToken, $collection, $image);
    $share = $this->createCollectionShare($this->ownerToken, $collection);
    $this->unpublishShare($this->ownerToken, $share);

    self::assertSame(404, $this->apiRequest('GET', $this->url($collection, $image)));
  }

  #[Test]
  public function anonymousCannotReadImageNotMemberOfCollection(): void {
    $this->setAccessSettings(['requireAuthForCollectionShares' => false]);
    $this->bootActors();
    [$collection] = $this->sharedCollectionWithImage();

    $orphan = $this->uploadImage($this->ownerToken, false);

    self::assertSame(404, $this->apiRequest('GET', $this->url($collection, $orphan)));
  }
}
