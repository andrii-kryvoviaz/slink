<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Access;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Test;
use Slink\Share\Domain\Enum\ShareableType;
use Slink\Share\Domain\Repository\ShareRepositoryInterface;
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

  #[Test]
  public function deletingCollectionRemovesItsPublication(): void {
    $this->setAccessSettings(['requireAuthForCollectionShares' => false]);
    $this->bootActors();
    $collection = $this->sharedCollection();

    /** @var ShareRepositoryInterface $shareRepository */
    $shareRepository = static::getContainer()->get(ShareRepositoryInterface::class);
    self::assertNotNull($shareRepository->findByShareable($collection, ShareableType::Collection));

    self::assertSame(
      204,
      $this->apiRequest(
        'DELETE',
        '/api/collection',
        $this->ownerToken,
        ['CONTENT_TYPE' => 'application/json'],
        \json_encode(['id' => $collection], JSON_THROW_ON_ERROR),
      ),
    );

    /** @var EntityManagerInterface $entityManager */
    $entityManager = static::getContainer()->get(EntityManagerInterface::class);
    $entityManager->clear();

    self::assertNull($shareRepository->findByShareable($collection, ShareableType::Collection));
  }

  #[Test]
  public function deletingCollectionWithImagesRevokesAllShares(): void {
    $this->setAccessSettings(['requireAuthForCollectionShares' => false]);
    $this->bootActors();

    $collection = $this->createCollection($this->ownerToken);
    $imageA = $this->uploadImage($this->ownerToken, false);
    $imageB = $this->uploadImage($this->ownerToken, false);
    $this->addImageToCollection($this->ownerToken, $collection, $imageA);
    $this->addImageToCollection($this->ownerToken, $collection, $imageB);

    $collectionShare = $this->createCollectionShare($this->ownerToken, $collection);
    $this->publishShare($this->ownerToken, $collectionShare);

    $imageShareA = $this->createImageShare($this->ownerToken, $imageA);
    $this->publishShare($this->ownerToken, $imageShareA);
    $imageShareB = $this->createImageShare($this->ownerToken, $imageB);
    $this->publishShare($this->ownerToken, $imageShareB);

    /** @var ShareRepositoryInterface $shareRepository */
    $shareRepository = static::getContainer()->get(ShareRepositoryInterface::class);
    self::assertNotNull($shareRepository->findByShareable($collection, ShareableType::Collection));
    self::assertNotNull($shareRepository->findByShareable($imageA, ShareableType::Image));
    self::assertNotNull($shareRepository->findByShareable($imageB, ShareableType::Image));

    self::assertSame(
      204,
      $this->apiRequest(
        'DELETE',
        '/api/collection',
        $this->ownerToken,
        ['CONTENT_TYPE' => 'application/json'],
        \json_encode(['id' => $collection, 'deleteImages' => true], JSON_THROW_ON_ERROR),
      ),
      'Delete failed: ' . (string) $this->client->getResponse()->getContent(),
    );

    /** @var EntityManagerInterface $entityManager */
    $entityManager = static::getContainer()->get(EntityManagerInterface::class);
    $entityManager->clear();

    self::assertNull(
      $shareRepository->findByShareable($collection, ShareableType::Collection),
      'Collection share must be revoked when the collection is deleted.',
    );
    self::assertNull(
      $shareRepository->findByShareable($imageA, ShareableType::Image),
      'Share for the first member image must be revoked when the collection is deleted with its images.',
    );
    self::assertNull(
      $shareRepository->findByShareable($imageB, ShareableType::Image),
      'Share for the second member image must be revoked when the collection is deleted with its images.',
    );
  }

  #[Test]
  public function deletingCollectionWithoutImagesKeepsIndependentImageShares(): void {
    $this->setAccessSettings(['requireAuthForCollectionShares' => false, 'requireAuthForMediaShares' => false]);
    $this->bootActors();

    $collection = $this->createCollection($this->ownerToken);
    $image = $this->uploadImage($this->ownerToken, false);
    $this->addImageToCollection($this->ownerToken, $collection, $image);

    $collectionShare = $this->createCollectionShare($this->ownerToken, $collection);
    $this->publishShare($this->ownerToken, $collectionShare);

    $imageShare = $this->createImageShare($this->ownerToken, $image);
    $this->publishShare($this->ownerToken, $imageShare);

    /** @var ShareRepositoryInterface $shareRepository */
    $shareRepository = static::getContainer()->get(ShareRepositoryInterface::class);
    self::assertNotNull($shareRepository->findByShareable($collection, ShareableType::Collection));
    self::assertNotNull($shareRepository->findByShareable($image, ShareableType::Image));

    self::assertSame(
      204,
      $this->apiRequest(
        'DELETE',
        '/api/collection',
        $this->ownerToken,
        ['CONTENT_TYPE' => 'application/json'],
        \json_encode(['id' => $collection], JSON_THROW_ON_ERROR),
      ),
      'Delete failed: ' . (string) $this->client->getResponse()->getContent(),
    );

    /** @var EntityManagerInterface $entityManager */
    $entityManager = static::getContainer()->get(EntityManagerInterface::class);
    $entityManager->clear();

    self::assertNull(
      $shareRepository->findByShareable($collection, ShareableType::Collection),
      'Collection share must be revoked when the collection is deleted.',
    );
    self::assertNotNull(
      $shareRepository->findByShareable($image, ShareableType::Image),
      'An independent image share must survive deletion of a collection that merely contained the image (deleteImages=false).',
    );
  }
}
