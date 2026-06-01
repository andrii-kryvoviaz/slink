<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Access;

use PHPUnit\Framework\Attributes\Test;
use Tests\Integration\Http\HttpTestCase;

final class ShareUnlockTest extends HttpTestCase {
  private string $ownerToken = '';

  private function bootOwner(): void {
    $this->createUser('owner@local.test', 'owneruser', self::PASSWORD);
    $this->ownerToken = $this->login('owneruser', self::PASSWORD);
  }

  /**
   * @return array{0: string, 1: string}
   */
  private function passwordImageShare(): array {
    $image = $this->uploadImage($this->ownerToken, false);
    $share = $this->createImageShare($this->ownerToken, $image);
    $this->setSharePassword($this->ownerToken, $share, self::SHARE_PASSWORD);
    $this->publishShare($this->ownerToken, $share);

    return [$share, $image];
  }

  /**
   * @return array{0: string, 1: string}
   */
  private function passwordCollectionShare(): array {
    $collection = $this->createCollection($this->ownerToken);
    $image = $this->uploadImage($this->ownerToken, false);
    $this->addImageToCollection($this->ownerToken, $collection, $image);
    $share = $this->createCollectionShare($this->ownerToken, $collection);
    $this->setSharePassword($this->ownerToken, $share, self::SHARE_PASSWORD);
    $this->publishShare($this->ownerToken, $share);

    return [$share, $collection];
  }

  #[Test]
  public function imageShareUnlockSucceedsAndGrantsFetchWhenMediaShareFlagOff(): void {
    $this->setAccessSettings(['requireAuthForMediaShares' => false]);
    $this->bootOwner();
    [$share, $image] = $this->passwordImageShare();

    [$unlockStatus, $cookies] = $this->unlockShare($share, self::SHARE_PASSWORD);
    self::assertContains($unlockStatus, [200, 204]);
    self::assertSame(200, $this->requestWithCookies('GET', \sprintf('/api/image/%s.png', $image), $cookies));
  }

  #[Test]
  public function anonymousImageShareUnlockIsHiddenWhenMediaShareFlagOn(): void {
    $this->setAccessSettings(['requireAuthForMediaShares' => true]);
    $this->bootOwner();
    [$share, $image] = $this->passwordImageShare();

    [$unlockStatus, $cookies] = $this->unlockShare($share, self::SHARE_PASSWORD);
    self::assertSame(404, $unlockStatus);
    self::assertSame(404, $this->requestWithCookies('GET', \sprintf('/api/image/%s.png', $image), $cookies));
  }

  #[Test]
  public function collectionShareUnlockSucceedsAndGrantsFetchWhenFlagOff(): void {
    $this->setAccessSettings(['requireAuthForCollectionShares' => false]);
    $this->bootOwner();
    [$share, $collection] = $this->passwordCollectionShare();

    [$unlockStatus, $cookies] = $this->unlockShare($share, self::SHARE_PASSWORD);
    self::assertContains($unlockStatus, [200, 204]);
    self::assertSame(200, $this->requestWithCookies('GET', \sprintf('/api/collection/%s', $collection), $cookies));
  }

  #[Test]
  public function anonymousCollectionShareUnlockIsHiddenWhenFlagOn(): void {
    $this->setAccessSettings(['requireAuthForCollectionShares' => true]);
    $this->bootOwner();
    [$share, $collection] = $this->passwordCollectionShare();

    [$unlockStatus, $cookies] = $this->unlockShare($share, self::SHARE_PASSWORD);
    self::assertSame(404, $unlockStatus);
    self::assertSame(401, $this->requestWithCookies('GET', \sprintf('/api/collection/%s', $collection), $cookies));
  }
}
