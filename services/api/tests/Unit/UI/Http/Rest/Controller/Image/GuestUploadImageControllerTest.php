<?php

declare(strict_types=1);

namespace Tests\Unit\UI\Http\Rest\Controller\Image;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Image\Application\Command\UploadImage\UploadImageCommand;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Application\Command\CommandBusInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use UI\Http\Rest\Controller\Image\GuestUploadImageController;

final class GuestUploadImageControllerTest extends TestCase {

  #[Test]
  public function itThrowsExceptionWhenGuestUploadsDisabled(): void {
    $this->expectException(AccessDeniedHttpException::class);
    $this->expectExceptionMessage('Guest uploads are not allowed');

    $configProvider = $this->createMock(ConfigurationProviderInterface::class);
    $configProvider->method('get')
      ->with('access.allowGuestUploads')
      ->willReturn(false);

    $controller = new GuestUploadImageController($configProvider);

    $command = $this->createMock(UploadImageCommand::class);

    $controller($command);
  }

  #[Test]
  public function itAllowsUploadWhenGuestUploadsEnabled(): void {
    $configProvider = $this->createMock(ConfigurationProviderInterface::class);
    $configProvider->method('get')
      ->with('access.allowGuestUploads')
      ->willReturn(true);

    $commandBus = $this->createMock(CommandBusInterface::class);
    $commandBus->expects($this->once())
      ->method('handle');

    $controller = new GuestUploadImageController($configProvider);

    $reflection = new \ReflectionClass($controller);
    $property = $reflection->getProperty('commandBus');
    $property->setAccessible(true);
    $property->setValue($controller, $commandBus);

    $command = $this->createMock(UploadImageCommand::class);
    $command->method('getId')
      ->willReturn($this->createMock(\Slink\Shared\Domain\ValueObject\ID::class));

    $response = $controller($command);

    $this->assertEquals(201, $response->getStatusCode());
    $this->assertEquals('/explore', $response->headers->get('location'));
  }
}
