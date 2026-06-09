<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Access;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Test;
use Slink\Share\Domain\Enum\ShareableType;
use Slink\Share\Domain\Repository\ShareRepositoryInterface;
use Tests\Integration\Http\HttpTestCase;

final class DirectImageAccessTest extends HttpTestCase {
  private string $ownerToken = '';
  private string $otherToken = '';

  private function bootActors(): void {
    $this->createUser('owner@local.test', 'owneruser', self::PASSWORD);
    $this->createUser('viewer@local.test', 'vieweruser', self::PASSWORD);

    $this->ownerToken = $this->login('owneruser', self::PASSWORD);
    $this->otherToken = $this->login('vieweruser', self::PASSWORD);
  }

  private function url(string $imageId): string {
    return \sprintf('/api/image/%s.png', $imageId);
  }

  private function sharedImage(): string {
    $image = $this->uploadImage($this->ownerToken, false);
    $share = $this->createImageShare($this->ownerToken, $image);
    $this->publishShare($this->ownerToken, $share);

    return $image;
  }

  #[Test]
  public function ownerReadsPrivateUnsharedImageRegardlessOfMediaShareFlag(): void {
    $this->setAccessSettings(['requireAuthForMediaShares' => false]);
    $this->bootActors();
    $image = $this->uploadImage($this->ownerToken, false);

    self::assertSame(200, $this->apiRequest('GET', $this->url($image), $this->ownerToken));

    $this->setAccessSettings(['requireAuthForMediaShares' => true]);
    self::assertSame(200, $this->apiRequest('GET', $this->url($image), $this->ownerToken));
  }

  #[Test]
  public function authenticatedNonOwnerCannotReadPublicImageWithoutShare(): void {
    $this->setAccessSettings(['requireAuthForMediaShares' => false]);
    $this->bootActors();
    $image = $this->uploadImage($this->ownerToken, true);

    self::assertSame(404, $this->apiRequest('GET', $this->url($image), $this->otherToken));
  }

  #[Test]
  public function authenticatedNonOwnerCannotReadPrivateImageWithoutShare(): void {
    $this->setAccessSettings(['requireAuthForMediaShares' => false]);
    $this->bootActors();
    $image = $this->uploadImage($this->ownerToken, false);

    self::assertSame(404, $this->apiRequest('GET', $this->url($image), $this->otherToken));
  }

  #[Test]
  public function authenticatedNonOwnerReadsPublishedShareRegardlessOfMediaShareFlag(): void {
    $this->setAccessSettings(['requireAuthForMediaShares' => false]);
    $this->bootActors();
    $image = $this->sharedImage();

    self::assertSame(200, $this->apiRequest('GET', $this->url($image), $this->otherToken));

    $this->setAccessSettings(['requireAuthForMediaShares' => true]);
    self::assertSame(200, $this->apiRequest('GET', $this->url($image), $this->otherToken));
  }

  #[Test]
  public function anonymousCannotReadPublicImageWithoutShare(): void {
    $this->setAccessSettings(['requireAuthForMediaShares' => false]);
    $this->bootActors();
    $image = $this->uploadImage($this->ownerToken, true);

    self::assertSame(404, $this->apiRequest('GET', $this->url($image)));

    $this->setAccessSettings(['requireAuthForMediaShares' => true]);
    self::assertSame(404, $this->apiRequest('GET', $this->url($image)));
  }

  #[Test]
  public function anonymousReadsPublishedShareWhenMediaShareFlagOff(): void {
    $this->setAccessSettings(['requireAuthForMediaShares' => false]);
    $this->bootActors();
    $image = $this->sharedImage();

    self::assertSame(200, $this->apiRequest('GET', $this->url($image)));
  }

  #[Test]
  public function anonymousIsLockedOutOfPasswordProtectedShare(): void {
    $this->setAccessSettings(['requireAuthForMediaShares' => false]);
    $this->bootActors();

    $image = $this->uploadImage($this->ownerToken, false);
    $share = $this->createImageShare($this->ownerToken, $image);
    $this->setSharePassword($this->ownerToken, $share, self::SHARE_PASSWORD);
    $this->publishShare($this->ownerToken, $share);

    self::assertSame(423, $this->apiRequest('GET', $this->url($image)));
  }

  #[Test]
  public function anonymousReadsPasswordProtectedShareAfterUnlock(): void {
    $this->setAccessSettings(['requireAuthForMediaShares' => false]);
    $this->bootActors();

    $image = $this->uploadImage($this->ownerToken, false);
    $share = $this->createImageShare($this->ownerToken, $image);
    $this->setSharePassword($this->ownerToken, $share, self::SHARE_PASSWORD);
    $this->publishShare($this->ownerToken, $share);

    [$unlockStatus, $cookies] = $this->unlockShare($share, self::SHARE_PASSWORD);
    self::assertContains($unlockStatus, [200, 204]);
    self::assertSame(200, $this->requestWithCookies('GET', $this->url($image), $cookies));
  }

  #[Test]
  public function anonymousGetsGoneForExpiredShare(): void {
    $this->setAccessSettings(['requireAuthForMediaShares' => false]);
    $this->bootActors();

    $image = $this->uploadImage($this->ownerToken, false);
    $share = $this->createImageShare($this->ownerToken, $image);
    $this->publishShare($this->ownerToken, $share);
    $this->setShareExpiration($this->ownerToken, $share, $this->futureIso(1));

    \sleep(2);

    self::assertSame(410, $this->apiRequest('GET', $this->url($image)));
  }

  #[Test]
  public function anonymousCannotReadUnpublishedShare(): void {
    $this->setAccessSettings(['requireAuthForMediaShares' => false]);
    $this->bootActors();

    $image = $this->uploadImage($this->ownerToken, false);
    $this->createImageShare($this->ownerToken, $image);

    self::assertSame(404, $this->apiRequest('GET', $this->url($image)));
  }

  #[Test]
  public function anonymousIsHiddenFromPublishedShareWhenMediaShareFlagOn(): void {
    $this->setAccessSettings(['requireAuthForMediaShares' => true]);
    $this->bootActors();
    $image = $this->sharedImage();

    self::assertSame(404, $this->apiRequest('GET', $this->url($image)));
  }

  #[Test]
  public function deletingImageRemovesItsPublication(): void {
    $this->setAccessSettings(['requireAuthForMediaShares' => false]);
    $this->bootActors();
    $image = $this->sharedImage();

    /** @var ShareRepositoryInterface $shareRepository */
    $shareRepository = static::getContainer()->get(ShareRepositoryInterface::class);
    self::assertNotNull($shareRepository->findByShareable($image, ShareableType::Image));

    self::assertContains(
      $this->apiRequest('DELETE', \sprintf('/api/image/%s', $image), $this->ownerToken),
      [200, 204],
      'Delete failed: ' . (string) $this->client->getResponse()->getContent(),
    );

    /** @var EntityManagerInterface $entityManager */
    $entityManager = static::getContainer()->get(EntityManagerInterface::class);
    $entityManager->clear();

    self::assertNull($shareRepository->findByShareable($image, ShareableType::Image));
  }

  #[Test]
  public function deletingImageRevokesUnpublishedShare(): void {
    $this->setAccessSettings(['requireAuthForMediaShares' => false]);
    $this->bootActors();

    $image = $this->uploadImage($this->ownerToken, false);
    $share = $this->createImageShare($this->ownerToken, $image);
    $this->unpublishShare($this->ownerToken, $share);

    /** @var ShareRepositoryInterface $shareRepository */
    $shareRepository = static::getContainer()->get(ShareRepositoryInterface::class);
    self::assertNotNull($shareRepository->findByShareable($image, ShareableType::Image));

    self::assertContains(
      $this->apiRequest('DELETE', \sprintf('/api/image/%s', $image), $this->ownerToken),
      [200, 204],
      'Delete failed: ' . (string) $this->client->getResponse()->getContent(),
    );

    /** @var EntityManagerInterface $entityManager */
    $entityManager = static::getContainer()->get(EntityManagerInterface::class);
    $entityManager->clear();

    self::assertNull(
      $shareRepository->findByShareable($image, ShareableType::Image),
      'Unpublished share for a deleted image must also be revoked.',
    );
  }
}
