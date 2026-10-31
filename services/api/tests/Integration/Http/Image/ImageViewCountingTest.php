<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Image;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\Integration\Http\HttpTestCase;

#[Group('sc-1203')]
final class ImageViewCountingTest extends HttpTestCase {
  private const string UNKNOWN_ID = '11111111-2222-4333-8444-555555555555';

  private string $ownerToken = '';
  private string $nonOwnerToken = '';

  protected function setUp(): void {
    parent::setUp();

    $this->setAccessSettings(['allowUnauthenticatedAccess' => true, 'allowGuestUploads' => true]);
    $this->createUser('count-owner@local.test', 'countowner', self::PASSWORD);
    $this->createUser('count-non-owner@local.test', 'countnonowner', self::PASSWORD);
    $this->ownerToken = $this->login('countowner', self::PASSWORD);
    $this->nonOwnerToken = $this->login('countnonowner', self::PASSWORD);
  }

  /**
   * @return iterable<string, array{string}>
   */
  public static function contentRoutes(): iterable {
    yield 'image' => ['image'];
    yield 'public image' => ['public'];
    yield 'collection item' => ['collection'];
  }

  private function contentPath(string $route, string $imageId): string {
    return match ($route) {
      'image' => $this->publishedShareOf($imageId),
      'public' => \sprintf('/api/image/public/%s.png', $imageId),
      'collection' => \sprintf(
        '/api/image/collection/%s/items/%s.png',
        $this->sharedCollectionHolding($imageId),
        $imageId,
      ),
      default => self::fail('Unknown route ' . $route),
    };
  }

  private function publishedShareOf(string $imageId): string {
    $this->publishShare($this->ownerToken, $this->createImageShare($this->ownerToken, $imageId));

    return \sprintf('/api/image/%s.png', $imageId);
  }

  private function sharedCollectionHolding(string $imageId): string {
    $collectionId = $this->createCollection($this->ownerToken);
    $this->addImageToCollection($this->ownerToken, $collectionId, $imageId);
    $this->publishShare($this->ownerToken, $this->createCollectionShare($this->ownerToken, $collectionId));

    return $collectionId;
  }

  private function views(string $imageId): int {
    /** @var EntityManagerInterface $entityManager */
    $entityManager = static::getContainer()->get(EntityManagerInterface::class);

    return (int) $entityManager->getConnection()->fetchOne('SELECT views FROM "image" WHERE uuid = ?', [$imageId]);
  }

  private function guestImage(): string {
    $this->client->request('POST', '/api/upload', ['isPublic' => 'true'], ['image' => $this->sampleImage()]);
    self::assertContains($this->client->getResponse()->getStatusCode(), [200, 201]);

    return $this->extractId((string) $this->client->getResponse()->getContent());
  }

  #[Test]
  #[DataProvider('contentRoutes')]
  public function theOwnersOwnViewDoesNotCount(string $route): void {
    $imageId = $this->uploadImage($this->ownerToken, true);

    self::assertSame(200, $this->apiRequest('GET', $this->contentPath($route, $imageId), $this->ownerToken));

    self::assertSame(0, $this->views($imageId));
  }

  #[Test]
  #[DataProvider('contentRoutes')]
  public function aNonOwnersViewCounts(string $route): void {
    $imageId = $this->uploadImage($this->ownerToken, true);

    self::assertSame(200, $this->apiRequest('GET', $this->contentPath($route, $imageId), $this->nonOwnerToken));

    self::assertSame(1, $this->views($imageId));
  }

  #[Test]
  #[DataProvider('contentRoutes')]
  public function anAnonymousViewCounts(string $route): void {
    $imageId = $this->uploadImage($this->ownerToken, true);

    self::assertSame(200, $this->apiRequest('GET', $this->contentPath($route, $imageId)));

    self::assertSame(1, $this->views($imageId));
  }

  #[Test]
  public function anyViewerOfAGuestOwnedImageCounts(): void {
    $imageId = $this->guestImage();
    $path = '/api/image/public/' . $imageId . '.png';

    self::assertSame(200, $this->apiRequest('GET', $path, $this->ownerToken));
    self::assertSame(200, $this->apiRequest('GET', $path));

    self::assertSame(2, $this->views($imageId));
  }

  #[Test]
  public function aDeniedContentRequestCountsNothing(): void {
    $imageId = $this->uploadImage($this->ownerToken, false);

    self::assertNotSame(200, $this->apiRequest('GET', '/api/image/public/' . $imageId . '.png', $this->nonOwnerToken));

    self::assertSame(0, $this->views($imageId));
  }

  #[Test]
  public function aMissingImageAnswersNotFoundAndCountsNothing(): void {
    self::assertSame(404, $this->apiRequest('GET', '/api/image/public/' . self::UNKNOWN_ID . '.png'));

    self::assertSame(0, $this->countRowsByColumn('image', 'uuid', self::UNKNOWN_ID));
  }
}
