<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Application\Service\Upload\Stage;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Slink\Collection\Domain\Collection;
use Slink\Collection\Domain\Enum\CollectionAccess;
use Slink\Collection\Domain\Exception\CollectionAccessDeniedException;
use Slink\Collection\Domain\Repository\CollectionStoreRepositoryInterface;
use Slink\Collection\Domain\ValueObject\CollectionDescription;
use Slink\Collection\Domain\ValueObject\CollectionName;
use Slink\Image\Application\Service\Upload\Stage\AuthorizeCollectionsStage;
use Slink\Shared\Domain\ValueObject\ID;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Tests\Unit\Slink\Image\Application\Service\Upload\UploadContextFactoryTrait;

class AuthorizeCollectionsStageTest extends TestCase {
  use UploadContextFactoryTrait;

  /**
   * @throws Exception
   */
  #[Test]
  public function itPassesThroughWhenNoCollectionIdsGiven(): void {
    $access = $this->createMock(AuthorizationCheckerInterface::class);
    $access->expects($this->never())->method('isGranted');

    $stage = new AuthorizeCollectionsStage($this->createStub(CollectionStoreRepositoryInterface::class), $access);
    $context = $this->uploadContext($this->createStub(File::class), userId: ID::generate());

    $this->assertSame($context, $stage->process($context));
  }

  /**
   * @throws Exception
   */
  #[Test]
  public function itPassesThroughForAnonymousUpload(): void {
    $access = $this->createMock(AuthorizationCheckerInterface::class);
    $access->expects($this->never())->method('isGranted');

    $stage = new AuthorizeCollectionsStage($this->createStub(CollectionStoreRepositoryInterface::class), $access);
    $context = $this->uploadContext($this->createStub(File::class), collectionIds: [ID::generate()->toString()]);

    $this->assertSame($context, $stage->process($context));
  }

  /**
   * @throws Exception
   */
  #[Test]
  public function itThrowsWhenCollectionAccessIsDenied(): void {
    $access = $this->createStub(AuthorizationCheckerInterface::class);
    $access->method('isGranted')->willReturn(false);

    $stage = new AuthorizeCollectionsStage($this->collectionStore(), $access);
    $context = $this->uploadContext(
      $this->createStub(File::class),
      userId: ID::generate(),
      collectionIds: [ID::generate()->toString()],
    );

    $this->expectException(CollectionAccessDeniedException::class);

    $stage->process($context);
  }

  /**
   * @throws Exception
   */
  #[Test]
  public function itChecksManageItemsOncePerCollection(): void {
    $access = $this->createMock(AuthorizationCheckerInterface::class);
    $access
      ->expects($this->exactly(2))
      ->method('isGranted')
      ->with(CollectionAccess::ManageItems, $this->isInstanceOf(Collection::class))
      ->willReturn(true);

    $stage = new AuthorizeCollectionsStage($this->collectionStore(), $access);
    $context = $this->uploadContext(
      $this->createStub(File::class),
      userId: ID::generate(),
      collectionIds: [ID::generate()->toString(), ID::generate()->toString()],
    );

    $this->assertSame($context, $stage->process($context));
  }

  private function collectionStore(): CollectionStoreRepositoryInterface {
    $store = $this->createStub(CollectionStoreRepositoryInterface::class);
    $store->method('get')->willReturnCallback(static fn(ID $id): Collection => Collection::create(
      $id,
      ID::generate(),
      CollectionName::fromString('Test'),
      CollectionDescription::fromString('Test'),
    ));

    return $store;
  }
}
