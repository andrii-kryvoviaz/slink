<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Application\Command\BatchImages\Operation;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Slink\Image\Application\Command\BatchImages\BatchImagesCommand;
use Slink\Image\Application\Command\BatchImages\Operation\UpdateVisibilityOperation;
use Slink\Image\Domain\Image;
use Slink\Image\Domain\ValueObject\ImageAttributes;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Domain\ValueObject\ID;

final class UpdateVisibilityOperationTest extends TestCase {
  private const USER_ID = '123e4567-e89b-12d3-a456-426614174000';

  /**
   * @throws Exception
   */
  #[Test]
  public function itSupportsCommandWithIsPublic(): void {
    $command = new BatchImagesCommand([], isPublic: true);
    $configProvider = $this->createStub(ConfigurationProviderInterface::class);

    $operation = new UpdateVisibilityOperation($configProvider);

    $this->assertTrue($operation->supports($command));
  }

  /**
   * @throws Exception
   */
  #[Test]
  public function itDoesNotSupportCommandWithoutIsPublic(): void {
    $command = new BatchImagesCommand([]);
    $configProvider = $this->createStub(ConfigurationProviderInterface::class);

    $operation = new UpdateVisibilityOperation($configProvider);

    $this->assertFalse($operation->supports($command));
  }

  /**
   * @throws Exception
   */
  #[Test]
  public function itUpdatesImageVisibility(): void {
    $command = new BatchImagesCommand([], isPublic: false);
    $userId = ID::fromString(self::USER_ID);

    $configProvider = $this->createStub(ConfigurationProviderInterface::class);
    $configProvider->method('get')->willReturn(false);

    $attributes = ImageAttributes::create('test.jpg', 'desc', true);

    $image = $this->createMock(Image::class);
    $image->method('getAttributes')->willReturn($attributes);
    $image->expects($this->once())->method('updateAttributes')->with(
      $this->callback(fn (ImageAttributes $attr) => $attr->isPublic() === false)
    );

    $operation = new UpdateVisibilityOperation($configProvider);
    $operation->apply($image, $command, $userId);
  }

  /**
   * @throws Exception
   */
  #[Test]
  public function itForcesPublicWhenConfigRequires(): void {
    $command = new BatchImagesCommand([], isPublic: false);
    $userId = ID::fromString(self::USER_ID);

    $configProvider = $this->createStub(ConfigurationProviderInterface::class);
    $configProvider->method('get')->willReturn(true);

    $attributes = ImageAttributes::create('test.jpg', 'desc', false);

    $image = $this->createMock(Image::class);
    $image->method('getAttributes')->willReturn($attributes);
    $image->expects($this->once())->method('updateAttributes')->with(
      $this->callback(fn (ImageAttributes $attr) => $attr->isPublic() === true)
    );

    $operation = new UpdateVisibilityOperation($configProvider);
    $operation->apply($image, $command, $userId);
  }
}
