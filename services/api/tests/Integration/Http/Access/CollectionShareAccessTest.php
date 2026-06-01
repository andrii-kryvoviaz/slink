<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Access;

use PHPUnit\Framework\Attributes\Test;
use Tests\Integration\Http\HttpTestCase;

final class CollectionShareAccessTest extends HttpTestCase {
  private string $ownerToken = '';
  private string $otherToken = '';

  private function bootActors(): void {
    $this->createUser('owner@local.test', 'owneruser', self::PASSWORD);
    $this->createUser('viewer@local.test', 'vieweruser', self::PASSWORD);

    $this->ownerToken = $this->login('owneruser', self::PASSWORD);
    $this->otherToken = $this->login('vieweruser', self::PASSWORD);
  }

  private function sharedCollection(): string {
    $collection = $this->createCollection($this->ownerToken);
    $image = $this->uploadImage($this->ownerToken, false);
    $this->addImageToCollection($this->ownerToken, $collection, $image);
    $share = $this->createCollectionShare($this->ownerToken, $collection);
    $this->publishShare($this->ownerToken, $share);

    return $collection;
  }

  private function url(string $collectionId): string {
    return \sprintf('/api/collection/%s', $collectionId);
  }

  #[Test]
  public function ownerReadsCollectionRegardlessOfFlag(): void {
    $this->setAccessSettings(['requireAuthForCollectionShares' => false]);
    $this->bootActors();
    $collection = $this->sharedCollection();

    self::assertSame(200, $this->apiRequest('GET', $this->url($collection), $this->ownerToken));

    $this->setAccessSettings(['requireAuthForCollectionShares' => true]);
    self::assertSame(200, $this->apiRequest('GET', $this->url($collection), $this->ownerToken));
  }

  #[Test]
  public function authenticatedNonOwnerReadsSharedCollectionRegardlessOfFlag(): void {
    $this->setAccessSettings(['requireAuthForCollectionShares' => false]);
    $this->bootActors();
    $collection = $this->sharedCollection();

    self::assertSame(200, $this->apiRequest('GET', $this->url($collection), $this->otherToken));

    $this->setAccessSettings(['requireAuthForCollectionShares' => true]);
    self::assertSame(200, $this->apiRequest('GET', $this->url($collection), $this->otherToken));
  }

  #[Test]
  public function anonymousReadsSharedCollectionWhenFlagOff(): void {
    $this->setAccessSettings(['requireAuthForCollectionShares' => false]);
    $this->bootActors();
    $collection = $this->sharedCollection();

    self::assertSame(200, $this->apiRequest('GET', $this->url($collection)));
  }

  #[Test]
  public function anonymousIsUnauthorizedForSharedCollectionWhenFlagOn(): void {
    $this->setAccessSettings(['requireAuthForCollectionShares' => true]);
    $this->bootActors();
    $collection = $this->sharedCollection();

    self::assertSame(401, $this->apiRequest('GET', $this->url($collection)));
  }

  #[Test]
  public function anonymousIsLockedOutOfPasswordProtectedCollection(): void {
    $this->setAccessSettings(['requireAuthForCollectionShares' => false]);
    $this->bootActors();

    $collection = $this->createCollection($this->ownerToken);
    $image = $this->uploadImage($this->ownerToken, false);
    $this->addImageToCollection($this->ownerToken, $collection, $image);
    $share = $this->createCollectionShare($this->ownerToken, $collection);
    $this->setSharePassword($this->ownerToken, $share, self::SHARE_PASSWORD);
    $this->publishShare($this->ownerToken, $share);

    self::assertSame(423, $this->apiRequest('GET', $this->url($collection)));
  }

  #[Test]
  public function anonymousReadsPasswordProtectedCollectionAfterUnlock(): void {
    $this->setAccessSettings(['requireAuthForCollectionShares' => false]);
    $this->bootActors();

    $collection = $this->createCollection($this->ownerToken);
    $image = $this->uploadImage($this->ownerToken, false);
    $this->addImageToCollection($this->ownerToken, $collection, $image);
    $share = $this->createCollectionShare($this->ownerToken, $collection);
    $this->setSharePassword($this->ownerToken, $share, self::SHARE_PASSWORD);
    $this->publishShare($this->ownerToken, $share);

    [, $cookies] = $this->unlockShare($share, self::SHARE_PASSWORD);

    self::assertSame(200, $this->requestWithCookies('GET', $this->url($collection), $cookies));
  }

  #[Test]
  public function anonymousGetsGoneForExpiredCollection(): void {
    $this->setAccessSettings(['requireAuthForCollectionShares' => false]);
    $this->bootActors();

    $collection = $this->createCollection($this->ownerToken);
    $image = $this->uploadImage($this->ownerToken, false);
    $this->addImageToCollection($this->ownerToken, $collection, $image);
    $share = $this->createCollectionShare($this->ownerToken, $collection);
    $this->publishShare($this->ownerToken, $share);
    $this->setShareExpiration($this->ownerToken, $share, $this->futureIso(1));

    \sleep(2);

    self::assertSame(410, $this->apiRequest('GET', $this->url($collection)));
  }

  #[Test]
  public function anonymousCannotReadUnpublishedCollection(): void {
    $this->setAccessSettings(['requireAuthForCollectionShares' => false]);
    $this->bootActors();

    $collection = $this->createCollection($this->ownerToken);
    $image = $this->uploadImage($this->ownerToken, false);
    $this->addImageToCollection($this->ownerToken, $collection, $image);
    $share = $this->createCollectionShare($this->ownerToken, $collection);
    $this->unpublishShare($this->ownerToken, $share);

    self::assertSame(404, $this->apiRequest('GET', $this->url($collection)));
  }
}
