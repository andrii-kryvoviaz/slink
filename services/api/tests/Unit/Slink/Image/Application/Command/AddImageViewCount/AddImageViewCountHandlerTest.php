<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Application\Command\AddImageViewCount;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Image\Application\Command\AddImageViewCount\AddImageViewCountCommand;
use Slink\Image\Application\Command\AddImageViewCount\AddImageViewCountHandler;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Shared\Domain\ValueObject\ID;

#[Group('sc-1203')]
final class AddImageViewCountHandlerTest extends TestCase {
  private const string IMAGE_ID = '123';

  /**
   * @return iterable<string, array{string|null, ID|null}>
   */
  public static function viewers(): iterable {
    yield 'anonymous' => [null, null];
    yield 'signed-in viewer' => ['viewer-456', ID::fromString('viewer-456')];
  }

  #[Test]
  #[DataProvider('viewers')]
  public function itHandsTheImageAndViewerToTheAtomicIncrementOnce(?string $viewerId, ?ID $expectedViewer): void {
    $imageRepository = $this->createMock(ImageRepositoryInterface::class);
    $imageRepository->expects($this->once())
      ->method($this->anything())
      ->with($this->equalTo(ID::fromString(self::IMAGE_ID)), $this->equalTo($expectedViewer));

    $handler = new AddImageViewCountHandler($imageRepository);

    $handler(new AddImageViewCountCommand(self::IMAGE_ID), $viewerId);
  }
}
