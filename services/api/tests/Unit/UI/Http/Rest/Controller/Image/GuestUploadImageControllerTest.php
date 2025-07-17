<?php

declare(strict_types=1);

namespace Tests\Unit\UI\Http\Rest\Controller\Image;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Image\Application\Command\UploadImage\UploadImageCommand;
use Slink\Shared\Application\Command\CommandBusInterface;
use UI\Http\Rest\Controller\Image\GuestUploadImageController;

final class GuestUploadImageControllerTest extends TestCase {

  #[Test]
  public function itAllowsUploadWhenGuestUploadsEnabled(): void {
    $commandBus = $this->createMock(CommandBusInterface::class);
    $commandBus->expects($this->once())
      ->method('handle');

    $controller = new GuestUploadImageController();

    $reflection = new \ReflectionClass($controller);
    $property = $reflection->getProperty('commandBus');
    $property->setAccessible(true);
    $property->setValue($controller, $commandBus);

    $command = $this->createMock(UploadImageCommand::class);
    $command->method('getId')
      ->willReturn($this->createMock(\Slink\Shared\Domain\ValueObject\ID::class));

    $response = $controller($command);

    $this->assertEquals(201, $response->getStatusCode());
  }
  
  #[Test]
  public function itCreatesSuccessfulResponse(): void {
    $commandBus = $this->createMock(CommandBusInterface::class);
    $commandBus->expects($this->once())
      ->method('handle');

    $controller = new GuestUploadImageController();

    $reflection = new \ReflectionClass($controller);
    $property = $reflection->getProperty('commandBus');
    $property->setAccessible(true);
    $property->setValue($controller, $commandBus);

    $command = $this->createMock(UploadImageCommand::class);
    $command->method('getId')
      ->willReturn($this->createMock(\Slink\Shared\Domain\ValueObject\ID::class));

    $response = $controller($command);

    $this->assertEquals(201, $response->getStatusCode());
  }
}
