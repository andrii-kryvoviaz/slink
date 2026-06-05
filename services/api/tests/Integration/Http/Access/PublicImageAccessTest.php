<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Access;

use PHPUnit\Framework\Attributes\Test;
use Tests\Integration\Http\HttpTestCase;

final class PublicImageAccessTest extends HttpTestCase {
  private string $ownerToken = '';
  private string $otherToken = '';
  private string $publicImage = '';
  private string $privateImage = '';

  private function prepareFixtures(): void {
    $this->createUser('owner@local.test', 'owneruser', self::PASSWORD);
    $this->createUser('viewer@local.test', 'vieweruser', self::PASSWORD);

    $this->ownerToken = $this->login('owneruser', self::PASSWORD);
    $this->otherToken = $this->login('vieweruser', self::PASSWORD);

    $this->publicImage = $this->uploadImage($this->ownerToken, true);
    $this->privateImage = $this->uploadImage($this->ownerToken, false);
  }

  #[Test]
  public function anonymousListIsUnauthorizedWhenGuestViewDisabled(): void {
    $this->setAccessSettings(['allowUnauthenticatedAccess' => false]);
    $this->prepareFixtures();

    self::assertSame(401, $this->apiRequest('GET', '/api/images'));
  }

  #[Test]
  public function anonymousCanViewPublicImageWhenGuestViewEnabled(): void {
    $this->setAccessSettings(['allowUnauthenticatedAccess' => true]);
    $this->prepareFixtures();

    self::assertSame(200, $this->apiRequest('GET', \sprintf('/api/image/public/%s.png', $this->publicImage)));
  }

  #[Test]
  public function anonymousCannotViewPrivateImageWhenGuestViewEnabled(): void {
    $this->setAccessSettings(['allowUnauthenticatedAccess' => true]);
    $this->prepareFixtures();

    self::assertSame(404, $this->apiRequest('GET', \sprintf('/api/image/public/%s.png', $this->privateImage)));
  }

  #[Test]
  public function authenticatedCanViewPublicImageEvenWhenGuestViewDisabled(): void {
    $this->setAccessSettings(['allowUnauthenticatedAccess' => false]);
    $this->prepareFixtures();

    self::assertSame(
      200,
      $this->apiRequest('GET', \sprintf('/api/image/public/%s.png', $this->publicImage), $this->otherToken),
    );
  }

  #[Test]
  public function authenticatedCannotViewPrivateImageThroughPublicEndpoint(): void {
    $this->setAccessSettings(['allowUnauthenticatedAccess' => false]);
    $this->prepareFixtures();

    self::assertSame(
      404,
      $this->apiRequest('GET', \sprintf('/api/image/public/%s.png', $this->privateImage), $this->otherToken),
    );
  }

  #[Test]
  public function ownerCannotViewPrivateImageThroughPublicEndpoint(): void {
    $this->setAccessSettings(['allowUnauthenticatedAccess' => true]);
    $this->prepareFixtures();

    self::assertSame(
      404,
      $this->apiRequest('GET', \sprintf('/api/image/public/%s.png', $this->privateImage), $this->ownerToken),
    );
  }
}
