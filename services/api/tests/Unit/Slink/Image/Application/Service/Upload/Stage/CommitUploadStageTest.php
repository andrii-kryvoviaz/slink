<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Application\Service\Upload\Stage;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Slink\Collection\Domain\Collection;
use Slink\Collection\Domain\Repository\CollectionStoreRepositoryInterface;
use Slink\Collection\Domain\ValueObject\CollectionDescription;
use Slink\Collection\Domain\ValueObject\CollectionName;
use Slink\Image\Application\Service\ImageTagAssigner;
use Slink\Image\Application\Service\Upload\Stage\CommitUploadStage;
use Slink\Image\Application\Service\Upload\UploadContext;
use Slink\Image\Domain\Context\ImageCreationContext;
use Slink\Image\Domain\Exception\DuplicateImageException;
use Slink\Image\Domain\Exception\UnauthorizedTagAccessException;
use Slink\Image\Domain\Repository\ImageStoreRepositoryInterface;
use Slink\Image\Domain\Specification\ImageDuplicateSpecificationInterface;
use Slink\Image\Domain\ValueObject\ImageFile;
use Slink\Image\Domain\ValueObject\ImageMetadata;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Shared\Domain\Exception\Date\DateTimeException;
use Slink\Shared\Domain\FileSystem\Storage\StorageInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Symfony\Component\HttpFoundation\File\File;
use Tests\Unit\Slink\Image\Application\Service\Upload\UploadContextFactoryTrait;

class CommitUploadStageTest extends TestCase {
  use UploadContextFactoryTrait;

  /**
   * @throws Exception
   * @throws DateTimeException
   */
  private function describedContext(?ID $userId = null): UploadContext {
    $file = $this->createStub(File::class);
    $file->method('getPathname')->willReturn('/tmp/test.webp');

    return $this
      ->uploadContext($file, userId: $userId ?? ID::generate(), description: 'desc')
      ->withDescribed(
        '123.webp',
        new ImageFile('/tmp/test.webp', 'image/webp', 'webp', 512),
        new ImageMetadata(512, 'image/webp', 800, 600, 'hash'),
      )
      ->withVisibility(true)
      ->withCollections([$this->loadedCollection()]);
  }

  private function loadedCollection(): Collection {
    return Collection::create(
      ID::generate(),
      ID::generate(),
      CollectionName::fromString('Test'),
      CollectionDescription::fromString('Test'),
    );
  }

  private function creationContext(): ImageCreationContext {
    return new ImageCreationContext($this->createStub(ImageDuplicateSpecificationInterface::class));
  }

  /**
   * @throws Exception
   * @throws DateTimeException
   */
  #[Test]
  public function itExecutesSideEffectsInSafeOrder(): void {
    $calls = [];

    $tagAssigner = $this->createMock(ImageTagAssigner::class);
    $tagAssigner->expects($this->once())->method('assign')->willReturnCallback(static function () use (&$calls): void {
      $calls[] = 'tags';
    });

    $storage = $this->createMock(StorageInterface::class);
    $storage->expects($this->once())->method('upload')->willReturnCallback(static function () use (&$calls): void {
      $calls[] = 'storage';
    });

    $imageRepository = $this->createMock(ImageStoreRepositoryInterface::class);
    $imageRepository->expects($this->once())->method('store')->willReturnCallback(static function () use (&$calls): void {
      $calls[] = 'store';
    });

    $collectionStore = $this->createMock(CollectionStoreRepositoryInterface::class);
    $collectionStore->expects($this->once())->method('store')->willReturnCallback(static function () use (&$calls): void {
      $calls[] = 'collections';
    });

    $stage = new CommitUploadStage($this->creationContext(), $tagAssigner, $storage, $imageRepository, $collectionStore);

    $stage->process($this->describedContext());

    $this->assertSame(['tags', 'storage', 'store', 'collections'], $calls);
  }

  /**
   * @throws Exception
   * @throws DateTimeException
   */
  #[Test]
  #[Group('sc-1607')]
  public function itAddsTheImageToEachLoadedCollection(): void {
    $stored = [];

    $collectionStore = $this->createMock(CollectionStoreRepositoryInterface::class);
    $collectionStore->expects($this->exactly(2))->method('store')->willReturnCallback(
      static function (Collection $collection) use (&$stored): void {
        $stored[] = $collection;
      },
    );

    $stage = new CommitUploadStage(
      $this->creationContext(),
      $this->createStub(ImageTagAssigner::class),
      $this->createStub(StorageInterface::class),
      $this->createStub(ImageStoreRepositoryInterface::class),
      $collectionStore,
    );

    $stage->process($this->describedContext()->withCollections([$this->loadedCollection(), $this->loadedCollection()]));

    $this->assertSame([1, 1], \array_map(static fn(Collection $collection): int => $collection->getItemCount(), $stored));
  }

  /**
   * @throws Exception
   * @throws DateTimeException
   */
  #[Test]
  public function itAbortsBeforeStorageWhenTagAuthorizationFails(): void {
    $userId = ID::generate();
    $tagAssigner = $this->createMock(ImageTagAssigner::class);
    $tagAssigner->expects($this->once())->method('assign')->willThrowException(
      new UnauthorizedTagAccessException(ID::generate(), $userId),
    );

    $storage = $this->createMock(StorageInterface::class);
    $storage->expects($this->never())->method('upload');

    $imageRepository = $this->createMock(ImageStoreRepositoryInterface::class);
    $imageRepository->expects($this->never())->method('store');

    $collectionStore = $this->createMock(CollectionStoreRepositoryInterface::class);
    $collectionStore->expects($this->never())->method('store');

    $stage = new CommitUploadStage($this->creationContext(), $tagAssigner, $storage, $imageRepository, $collectionStore);

    $this->expectException(UnauthorizedTagAccessException::class);

    $stage->process($this->describedContext($userId));
  }

  /**
   * @throws Exception
   * @throws DateTimeException
   */
  #[Test]
  public function itAbortsBeforeStoreWhenStorageFails(): void {
    $storage = $this->createMock(StorageInterface::class);
    $storage->expects($this->once())->method('upload')->willThrowException(new \RuntimeException('disk full'));

    $imageRepository = $this->createMock(ImageStoreRepositoryInterface::class);
    $imageRepository->expects($this->never())->method('store');

    $collectionStore = $this->createMock(CollectionStoreRepositoryInterface::class);
    $collectionStore->expects($this->never())->method('store');

    $stage = new CommitUploadStage(
      $this->creationContext(),
      $this->createStub(ImageTagAssigner::class),
      $storage,
      $imageRepository,
      $collectionStore,
    );

    $this->expectException(\RuntimeException::class);

    $stage->process($this->describedContext());
  }

  /**
   * @throws Exception
   * @throws DateTimeException
   */
  #[Test]
  public function itAbortsBeforeAnySideEffectWhenDuplicateDetected(): void {
    $existing = $this->createStub(ImageView::class);

    $duplicateSpec = $this->createMock(ImageDuplicateSpecificationInterface::class);
    $duplicateSpec->expects($this->once())->method('ensureNoDuplicate')->willThrowException(new DuplicateImageException($existing));

    $creationContext = new ImageCreationContext($duplicateSpec);

    $tagAssigner = $this->createMock(ImageTagAssigner::class);
    $tagAssigner->expects($this->never())->method('assign');

    $storage = $this->createMock(StorageInterface::class);
    $storage->expects($this->never())->method('upload');

    $imageRepository = $this->createMock(ImageStoreRepositoryInterface::class);
    $imageRepository->expects($this->never())->method('store');

    $collectionStore = $this->createMock(CollectionStoreRepositoryInterface::class);
    $collectionStore->expects($this->never())->method('store');

    $stage = new CommitUploadStage($creationContext, $tagAssigner, $storage, $imageRepository, $collectionStore);

    $this->expectException(DuplicateImageException::class);

    $stage->process($this->describedContext());
  }
}
