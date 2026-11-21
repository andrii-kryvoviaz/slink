<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Concurrency;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Slink\Image\Application\Command\AddImageViewCount\AddImageViewCountCommand;
use Slink\Shared\Application\Command\CommandBusInterface;
use Slink\User\Domain\Enum\UserStatus;

#[Group('sc-1203')]
final class ConcurrentImageViewTest extends ConcurrentRequestTestCase {
  private string $ownerId = '';
  private string $ownerToken = '';
  private string $nonOwnerToken = '';
  private string $adminToken = '';

  protected function setUp(): void {
    parent::setUp();

    $this->adminToken = $this->bootAdmin();
    $this->ownerId = $this->createUser('view-owner@local.test', 'viewowner', self::PASSWORD);
    $this->createUser('view-non-owner@local.test', 'viewnonowner', self::PASSWORD);
    $this->ownerToken = $this->login('viewowner', self::PASSWORD);
    $this->nonOwnerToken = $this->login('viewnonowner', self::PASSWORD);
  }

  /**
   * @return iterable<string, array{string}>
   */
  public static function contentRoutes(): iterable {
    yield 'image' => ['image'];
    yield 'public image' => ['public'];
    yield 'collection item' => ['collection'];
  }

  /**
   * @return iterable<string, array{array<string, bool|string>, string}>
   */
  public static function imageWrites(): iterable {
    yield 'edit' => [['isPublic' => true, 'description' => 'edited caption'], 'image_updated'];
    yield 'hide' => [['isPublic' => false, 'description' => 'edited caption'], 'image_removed'];
  }

  private function publishedImage(): string {
    $imageId = $this->uploadImage($this->ownerToken, true);
    $this->consumeOne();

    return $imageId;
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
    return (int) $this->imageColumn($imageId, 'views');
  }

  private function countViewFromAnotherKernel(string $imageId): void {
    /** @var CommandBusInterface $commandBus */
    $commandBus = $this->independentContainer()->get(CommandBusInterface::class);
    $commandBus->handle(new AddImageViewCountCommand($imageId));
  }

  private function storedBytesAt(string $path): string {
    self::assertSame(200, $this->apiRequest('GET', $path, $this->ownerToken));

    return $this->responseBytes();
  }

  private function viewFromAnotherKernel(string $imageId): void {
    $view = $this->requestFromAnotherKernel('GET', '/api/image/public/' . $imageId . '.png', $this->nonOwnerToken);

    self::assertSame(200, $view['status']);
  }

  #[Test]
  #[DataProvider('contentRoutes')]
  public function overlappingViewersAllGetTheStoredBytesAndEveryViewCounts(string $route): void {
    $imageId = $this->uploadImage($this->ownerToken, true);
    $path = $this->contentPath($route, $imageId);
    $storedBytes = $this->storedBytesAt($path);
    $overlapping = null;

    $this->beforeTheNextViewCount(function () use (&$overlapping, $path): void {
      $overlapping = $this->requestFromAnotherKernel('GET', $path, $this->nonOwnerToken);
    });

    self::assertSame(200, $this->apiRequest('GET', $path, $this->nonOwnerToken));
    self::assertSame($storedBytes, $this->responseBytes());
    self::assertIsArray($overlapping);
    self::assertSame(200, $overlapping['status']);
    self::assertSame($storedBytes, $overlapping['body']);
    self::assertSame(2, $this->views($imageId));
  }

  #[Test]
  #[DataProvider('contentRoutes')]
  public function aViewOfAnImageDeletedAfterItsBytesWereBuiltAnswersThoseBytesAndWritesNothing(string $route): void {
    $imageId = $this->publishedImage();
    $path = $this->contentPath($route, $imageId);
    $storedBytes = $this->storedBytesAt($path);

    $this->beforeTheNextViewCount(function () use ($imageId): void {
      $deletion = $this->requestFromAnotherKernel('DELETE', '/api/image/' . $imageId, $this->ownerToken);
      self::assertContains($deletion['status'], [200, 204]);
    });

    self::assertSame(200, $this->apiRequest('GET', $path, $this->nonOwnerToken));
    self::assertSame($storedBytes, $this->responseBytes());
    self::assertSame(0, $this->countRowsByColumn('image', 'uuid', $imageId));
    self::assertSame(1, $this->pending());
  }

  #[Test]
  public function aCountedViewLeavesTheVersionAndUpdatedAtAndEnqueuesNothing(): void {
    $imageId = $this->publishedImage();
    $this->sendJson('PATCH', '/api/image/' . $imageId, $this->ownerToken, ['isPublic' => true, 'description' => 'edited']);
    $this->consumeOne();
    $before = [$this->imageColumn($imageId, 'version'), $this->imageColumn($imageId, 'updated_at')];

    self::assertSame(200, $this->apiRequest('GET', '/api/image/public/' . $imageId . '.png', $this->nonOwnerToken));

    self::assertSame(1, $this->views($imageId));
    self::assertSame($before, [$this->imageColumn($imageId, 'version'), $this->imageColumn($imageId, 'updated_at')]);
    self::assertSame(0, $this->pending());
  }

  /**
   * @param array<string, bool|string> $write
   */
  #[Test]
  #[DataProvider('imageWrites')]
  public function aWriteThatLoadedTheImageBeforeAViewIsStoredAndTheViewStays(array $write, string $feedEvent): void {
    $imageId = $this->publishedImage();

    $this->beforeTheNextFlush(function () use ($imageId): void {
      $this->viewFromAnotherKernel($imageId);
    });

    self::assertContains($this->sendJson('PATCH', '/api/image/' . $imageId, $this->ownerToken, $write), [200, 204]);

    self::assertSame('edited caption', $this->imageColumn($imageId, 'description'));
    self::assertSame($write['isPublic'], (bool) $this->imageColumn($imageId, 'is_public'));
    self::assertSame(1, $this->views($imageId));
    self::assertSame(1, $this->pending());
    $this->consumeOne();
    $this->assertPublished($feedEvent, $imageId);
  }

  #[Test]
  public function aDeleteThatLoadedTheImageBeforeAViewStillRemovesTheRowOnce(): void {
    $imageId = $this->publishedImage();

    $this->beforeTheNextFlush(function () use ($imageId): void {
      $this->countViewFromAnotherKernel($imageId);
    });

    self::assertContains($this->apiRequest('DELETE', '/api/image/' . $imageId, $this->ownerToken), [200, 204]);

    self::assertSame(0, $this->countRowsByColumn('image', 'uuid', $imageId));
    self::assertSame(1, $this->pending());
    $this->consumeOne();
    $this->assertPublished('image_removed', $imageId);
  }

  #[Test]
  public function aPurgeDeletesEveryImageOfTheUserWhenAViewLandsBeforeItsFlush(): void {
    $viewedImageId = $this->uploadImage($this->ownerToken, true);
    $this->uploadImage($this->ownerToken, false);

    $this->beforeTheNextFlush(function () use ($viewedImageId): void {
      $this->countViewFromAnotherKernel($viewedImageId);
    });

    self::assertSame(204, $this->apiRequest('DELETE', '/api/user/' . $this->ownerId, $this->adminToken));

    self::assertSame(0, $this->countRowsByColumn('image', 'user_id', $this->ownerId));
  }

  #[Test]
  public function aPurgeThatLosesOnTheUserRowAnswersConflictRollsBackAndARerunFinishes(): void {
    $imageId = $this->uploadImage($this->ownerToken, false);

    $this->beforeTheNextFlush(function (): void {
      $suspension = $this->requestFromAnotherKernel('PATCH', '/api/user/status', $this->adminToken, [
        'id' => $this->ownerId,
        'status' => UserStatus::Suspended->value,
      ]);
      self::assertSame(200, $suspension['status'], $suspension['body']);
    });

    self::assertSame(409, $this->apiRequest('DELETE', '/api/user/' . $this->ownerId, $this->adminToken));
    self::assertSame(1, $this->countRowsByColumn('image', 'uuid', $imageId));
    self::assertSame(1, $this->countRowsByColumn('user', 'uuid', $this->ownerId));

    self::assertSame(204, $this->apiRequest('DELETE', '/api/user/' . $this->ownerId, $this->adminToken));
    self::assertSame(0, $this->countRowsByColumn('image', 'user_id', $this->ownerId));
  }

  #[Test]
  public function aHideCommittedWhileADeleteIsPendingLeavesExactlyOneRemovedAndTheRowGone(): void {
    $imageId = $this->publishedImage();

    $this->beforeTheNextFlush(function () use ($imageId): void {
      $hide = $this->requestFromAnotherKernel('PATCH', '/api/image/' . $imageId, $this->ownerToken, [
        'isPublic' => false,
        'description' => 'hidden',
      ]);
      self::assertContains($hide['status'], [200, 204]);
    });

    self::assertContains($this->apiRequest('DELETE', '/api/image/' . $imageId, $this->ownerToken), [200, 204]);

    self::assertSame(0, $this->countRowsByColumn('image', 'uuid', $imageId));
    self::assertSame(1, $this->pending());
    $this->consumeOne();
    $this->assertPublished('image_removed', $imageId);
  }

  #[Test]
  public function aViewCountUpdateThatFailsStillFailsTheRequest(): void {
    $imageId = $this->uploadImage($this->ownerToken, true);
    $connection = $this->connection();
    $connection->executeStatement(
      "CREATE TRIGGER fail_view_count BEFORE UPDATE OF views ON image BEGIN SELECT RAISE(ABORT, 'injected view count failure'); END",
    );

    try {
      $status = $this->apiRequest('GET', '/api/image/public/' . $imageId . '.png', $this->nonOwnerToken);
    } finally {
      $connection->executeStatement('DROP TRIGGER fail_view_count');
    }

    self::assertSame(500, $status);
    self::assertSame(0, $this->views($imageId));
  }
}
