<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Infrastructure\EventConsumer;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Image\Domain\Image;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Image\Domain\Repository\ImageStoreRepositoryInterface;
use Slink\Image\Infrastructure\EventConsumer\ImagePurger;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\Event\UserWasPurged;

final class ImagePurgerTest extends TestCase {

  #[Test]
  public function itForceDeletesEveryImageOwnedByPurgedUser(): void {
    $ownerId = ID::generate();

    $firstImageView = $this->createStub(ImageView::class);
    $firstImageView->method('getUuid')->willReturn('11111111-1111-1111-1111-111111111111');

    $secondImageView = $this->createStub(ImageView::class);
    $secondImageView->method('getUuid')->willReturn('22222222-2222-2222-2222-222222222222');

    $imageRepository = $this->createMock(ImageRepositoryInterface::class);
    $imageRepository->expects($this->once())
      ->method('findByUserId')
      ->with($this->callback(fn(ID $id) => $id->toString() === $ownerId->toString()))
      ->willReturn([$firstImageView, $secondImageView]);

    $firstImage = $this->createMock(Image::class);
    $firstImage->expects($this->once())->method('forceDelete')->with(false);

    $secondImage = $this->createMock(Image::class);
    $secondImage->expects($this->once())->method('forceDelete')->with(false);

    $imageStore = $this->createMock(ImageStoreRepositoryInterface::class);
    $imageStore->expects($this->exactly(2))
      ->method('get')
      ->willReturnCallback(fn(ID $id) => match ($id->toString()) {
        '11111111-1111-1111-1111-111111111111' => $firstImage,
        '22222222-2222-2222-2222-222222222222' => $secondImage,
        default => self::fail('Unexpected image id'),
      });
    $imageStore->expects($this->exactly(2))->method('store');

    $consumer = new ImagePurger($imageRepository, $imageStore);

    $consumer->handleUserWasPurged(new UserWasPurged($ownerId));
  }

  #[Test]
  public function itDoesNothingWhenUserOwnsNoImages(): void {
    $imageRepository = $this->createStub(ImageRepositoryInterface::class);
    $imageRepository->method('findByUserId')->willReturn([]);

    $imageStore = $this->createMock(ImageStoreRepositoryInterface::class);
    $imageStore->expects($this->never())->method('get');
    $imageStore->expects($this->never())->method('store');

    $consumer = new ImagePurger($imageRepository, $imageStore);

    $consumer->handleUserWasPurged(new UserWasPurged(ID::generate()));
  }
}
