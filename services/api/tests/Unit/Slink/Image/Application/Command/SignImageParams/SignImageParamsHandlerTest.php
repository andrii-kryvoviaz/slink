<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Application\Command\SignImageParams;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Slink\Image\Application\Command\SignImageParams\SignImageParamsCommand;
use Slink\Image\Application\Command\SignImageParams\SignImageParamsHandler;
use Slink\Image\Domain\Service\ImageUrlSignatureInterface;
use Slink\Image\Domain\ValueObject\SignedImageParams;

final class SignImageParamsHandlerTest extends TestCase {
  private MockObject&ImageUrlSignatureInterface $signatureService;
  private SignImageParamsHandler $handler;

  protected function setUp(): void {
    $this->signatureService = $this->createMock(ImageUrlSignatureInterface::class);
    $this->handler = new SignImageParamsHandler($this->signatureService);
  }

  #[Test]
  public function itHandlesCommandSuccessfully(): void {
    $imageId = '12345678-1234-1234-1234-123456789abc';
    $width = 800;
    $height = 600;
    $crop = true;
    $expectedSignature = 'generated_signature_hash';

    $command = new SignImageParamsCommand($imageId, $width, $height, $crop);

    $this->signatureService
      ->expects($this->once())
      ->method('sign')
      ->with($imageId, ['width' => $width, 'height' => $height, 'crop' => $crop])
      ->willReturn($expectedSignature);

    $result = ($this->handler)($command);

    $this->assertInstanceOf(SignedImageParams::class, $result);
    $this->assertEquals($imageId, $result->getImageId());
    $this->assertEquals($width, $result->getWidth());
    $this->assertEquals($height, $result->getHeight());
    $this->assertEquals($crop, $result->isCropped());
    $this->assertEquals($expectedSignature, $result->getSignature());
  }

  #[Test]
  public function itHandlesNullDimensions(): void {
    $imageId = '12345678-1234-1234-1234-123456789abc';
    $expectedSignature = 'generated_signature_hash';

    $command = new SignImageParamsCommand($imageId);

    $this->signatureService
      ->expects($this->once())
      ->method('sign')
      ->with($imageId, [])
      ->willReturn($expectedSignature);

    $result = ($this->handler)($command);

    $this->assertInstanceOf(SignedImageParams::class, $result);
    $this->assertNull($result->getWidth());
    $this->assertNull($result->getHeight());
    $this->assertFalse($result->isCropped());
  }

  #[Test]
  public function itHandlesPartialDimensions(): void {
    $imageId = '12345678-1234-1234-1234-123456789abc';
    $width = 800;
    $expectedSignature = 'generated_signature_hash';

    $command = new SignImageParamsCommand($imageId, $width, null, false);

    $this->signatureService
      ->expects($this->once())
      ->method('sign')
      ->with($imageId, ['width' => $width])
      ->willReturn($expectedSignature);

    $result = ($this->handler)($command);

    $this->assertEquals($width, $result->getWidth());
    $this->assertNull($result->getHeight());
  }
}
