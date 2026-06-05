<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Access;

use PHPUnit\Framework\Attributes\Test;
use Tests\Integration\Http\HttpTestCase;

final class CrossCuttingAccessTest extends HttpTestCase {
  private string $ownerToken = '';
  private string $otherToken = '';
  private string $adminToken = '';

  private function bootActors(): void {
    $this->createUser('owner@local.test', 'owneruser', self::PASSWORD);
    $this->createUser('viewer@local.test', 'vieweruser', self::PASSWORD);
    $adminId = $this->createUser('admin@local.test', 'adminuser', self::PASSWORD);
    $this->grantAdmin($adminId);

    $this->ownerToken = $this->login('owneruser', self::PASSWORD);
    $this->otherToken = $this->login('vieweruser', self::PASSWORD);
    $this->adminToken = $this->login('adminuser', self::PASSWORD);
  }

  #[Test]
  public function ownerBypassesEveryFlagAcrossEndpoints(): void {
    $this->setAccessSettings([
      'allowGuestUploads' => true,
      'allowUnauthenticatedAccess' => true,
      'requireAuthForMediaShares' => true,
      'requireAuthForCollectionShares' => true,
    ]);
    $this->bootActors();

    $image = $this->uploadImage($this->ownerToken, false);
    $collection = $this->createCollection($this->ownerToken);
    $collectionImage = $this->uploadImage($this->ownerToken, false);
    $this->addImageToCollection($this->ownerToken, $collection, $collectionImage);

    self::assertSame(200, $this->apiRequest('GET', \sprintf('/api/image/%s.png', $image), $this->ownerToken));
    self::assertSame(
      200,
      $this->apiRequest('GET', \sprintf('/api/image/collection/%s/items/%s.png', $collection, $collectionImage), $this->ownerToken),
    );
    self::assertSame(200, $this->apiRequest('GET', \sprintf('/api/collection/%s', $collection), $this->ownerToken));
  }

  #[Test]
  public function nonOwnerFetchingAnotherUsersPrivateImageIsNotFoundNotForbidden(): void {
    $this->setAccessSettings(['requireAuthForMediaShares' => false]);
    $this->bootActors();
    $image = $this->uploadImage($this->ownerToken, false);

    $status = $this->apiRequest('GET', \sprintf('/api/image/%s.png', $image), $this->otherToken);
    self::assertSame(404, $status);
    self::assertNotSame(403, $status);
  }

  #[Test]
  public function adminHasNoExistenceLeakOnAnotherUsersPrivateImage(): void {
    $this->setAccessSettings(['requireAuthForMediaShares' => false]);
    $this->bootActors();
    $image = $this->uploadImage($this->ownerToken, false);

    $status = $this->apiRequest('GET', \sprintf('/api/image/%s.png', $image), $this->adminToken);
    self::assertSame(404, $status);
    self::assertNotSame(403, $status);
  }

  #[Test]
  public function togglingMediaShareFlagDeniesAnonymousImageShareWith404(): void {
    $this->setAccessSettings(['requireAuthForMediaShares' => false]);
    $this->bootActors();

    $image = $this->uploadImage($this->ownerToken, false);
    $share = $this->createImageShare($this->ownerToken, $image);
    $this->publishShare($this->ownerToken, $share);

    self::assertSame(200, $this->apiRequest('GET', \sprintf('/api/image/%s.png', $image)));

    $this->setAccessSettings(['requireAuthForMediaShares' => true]);
    self::assertSame(404, $this->apiRequest('GET', \sprintf('/api/image/%s.png', $image)));
  }

  #[Test]
  public function togglingCollectionShareFlagDeniesAnonymousCollectionWith401(): void {
    $this->setAccessSettings(['requireAuthForCollectionShares' => false]);
    $this->bootActors();

    $collection = $this->createCollection($this->ownerToken);
    $image = $this->uploadImage($this->ownerToken, false);
    $this->addImageToCollection($this->ownerToken, $collection, $image);
    $share = $this->createCollectionShare($this->ownerToken, $collection);
    $this->publishShare($this->ownerToken, $share);

    self::assertSame(200, $this->apiRequest('GET', \sprintf('/api/collection/%s', $collection)));

    $this->setAccessSettings(['requireAuthForCollectionShares' => true]);
    self::assertSame(401, $this->apiRequest('GET', \sprintf('/api/collection/%s', $collection)));
  }

  #[Test]
  public function togglingGuestViewOffDeniesAnonymousListingWith401(): void {
    $this->setAccessSettings(['allowUnauthenticatedAccess' => true]);
    $this->bootActors();

    self::assertSame(200, $this->apiRequest('GET', '/api/images'));

    $this->setAccessSettings(['allowUnauthenticatedAccess' => false]);
    self::assertSame(401, $this->apiRequest('GET', '/api/images'));
  }
}
