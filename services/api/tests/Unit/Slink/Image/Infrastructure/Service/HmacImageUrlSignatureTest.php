<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Infrastructure\Service;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Image\Infrastructure\Service\HmacImageUrlSignature;

final class HmacImageUrlSignatureTest extends TestCase {
  private HmacImageUrlSignature $service;
  private string $secret = 'test-secret-key';

  protected function setUp(): void {
    $this->service = new HmacImageUrlSignature($this->secret);
  }

  #[Test]
  public function itGeneratesValidSignature(): void {
    $imageId = '12345678-1234-1234-1234-123456789abc';
    $params = [
      'width' => 800,
      'height' => 600,
      'crop' => true,
    ];

    $signature = $this->service->sign($imageId, $params);

    $this->assertEquals(64, strlen($signature));
    $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $signature);
  }

  #[Test]
  public function itVerifiesValidSignature(): void {
    $imageId = '12345678-1234-1234-1234-123456789abc';
    $params = [
      'width' => 800,
      'height' => 600,
      'crop' => true,
    ];

    $signature = $this->service->sign($imageId, $params);
    $isValid = $this->service->verify($imageId, $params, $signature);

    $this->assertTrue($isValid);
  }

  #[Test]
  public function itRejectsInvalidSignature(): void {
    $imageId = '12345678-1234-1234-1234-123456789abc';
    $params = [
      'width' => 800,
      'height' => 600,
      'crop' => true,
    ];

    $invalidSignature = 'invalid_signature_that_should_not_match';
    $isValid = $this->service->verify($imageId, $params, $invalidSignature);

    $this->assertFalse($isValid);
  }

  #[Test]
  public function itRejectsSignatureWithDifferentParameters(): void {
    $imageId = '12345678-1234-1234-1234-123456789abc';
    $params1 = [
      'width' => 800,
      'height' => 600,
      'crop' => true,
    ];
    $params2 = [
      'width' => 400,
      'height' => 300,
      'crop' => true,
    ];

    $signature = $this->service->sign($imageId, $params1);
    $isValid = $this->service->verify($imageId, $params2, $signature);

    $this->assertFalse($isValid);
  }

  #[Test]
  public function itRejectsSignatureWithDifferentImageId(): void {
    $imageId = '12345678-1234-1234-1234-123456789abc';
    $differentImageId = '87654321-4321-4321-4321-cba987654321';
    $params = [
      'width' => 800,
      'height' => 600,
      'crop' => true,
    ];

    $signature = $this->service->sign($imageId, $params);
    $isValid = $this->service->verify($differentImageId, $params, $signature);

    $this->assertFalse($isValid);
  }

  #[Test]
  public function itHandlesEmptyParams(): void {
    $imageId = '12345678-1234-1234-1234-123456789abc';
    $params = [];

    $signature = $this->service->sign($imageId, $params);
    $isValid = $this->service->verify($imageId, $params, $signature);

    $this->assertTrue($isValid);
  }

  #[Test]
  public function itHandlesPartialDimensions(): void {
    $imageId = '12345678-1234-1234-1234-123456789abc';

    $paramsWidthOnly = ['width' => 800];
    $signatureWidthOnly = $this->service->sign($imageId, $paramsWidthOnly);
    $isValidWidthOnly = $this->service->verify($imageId, $paramsWidthOnly, $signatureWidthOnly);
    $this->assertTrue($isValidWidthOnly);

    $paramsHeightOnly = ['height' => 600];
    $signatureHeightOnly = $this->service->sign($imageId, $paramsHeightOnly);
    $isValidHeightOnly = $this->service->verify($imageId, $paramsHeightOnly, $signatureHeightOnly);
    $this->assertTrue($isValidHeightOnly);
  }

  #[Test]
  public function itDifferentiatesCropParameter(): void {
    $imageId = '12345678-1234-1234-1234-123456789abc';
    $paramsCrop = ['width' => 800, 'height' => 600, 'crop' => true];
    $paramsNoCrop = ['width' => 800, 'height' => 600];

    $signatureCrop = $this->service->sign($imageId, $paramsCrop);
    $signatureNoCrop = $this->service->sign($imageId, $paramsNoCrop);

    $this->assertNotEquals($signatureCrop, $signatureNoCrop);
    $this->assertFalse($this->service->verify($imageId, $paramsNoCrop, $signatureCrop));
    $this->assertFalse($this->service->verify($imageId, $paramsCrop, $signatureNoCrop));
  }

  /**
   * @param array<string, mixed> $params
   */
  #[Test]
  #[DataProvider('imageParametersProvider')]
  public function itGeneratesConsistentSignatures(
    string $imageId,
    array $params
  ): void {
    $signature1 = $this->service->sign($imageId, $params);
    $signature2 = $this->service->sign($imageId, $params);

    $this->assertEquals($signature1, $signature2);
  }

  #[Test]
  public function itProtectsAgainstTimingAttacks(): void {
    $imageId = '12345678-1234-1234-1234-123456789abc';
    $params = ['width' => 800, 'height' => 600, 'crop' => true];

    $correctSignature = $this->service->sign($imageId, $params);
    $almostCorrectSignature = substr($correctSignature, 0, -1) . 'f';

    $startCorrect = microtime(true);
    $this->service->verify($imageId, $params, $correctSignature);
    $timeCorrect = microtime(true) - $startCorrect;

    $startWrong = microtime(true);
    $this->service->verify($imageId, $params, $almostCorrectSignature);
    $timeWrong = microtime(true) - $startWrong;

    $timeDifference = abs($timeCorrect - $timeWrong);
    $this->assertLessThan(0.0001, $timeDifference, 'Timing attack vulnerability detected');
  }

  #[Test]
  public function itSortsParamsForConsistentSignatures(): void {
    $imageId = '12345678-1234-1234-1234-123456789abc';
    $params1 = ['width' => 800, 'height' => 600, 'crop' => true];
    $params2 = ['crop' => true, 'height' => 600, 'width' => 800];

    $signature1 = $this->service->sign($imageId, $params1);
    $signature2 = $this->service->sign($imageId, $params2);

    $this->assertEquals($signature1, $signature2);
  }

  #[Test]
  public function itSupportsAdditionalParams(): void {
    $imageId = '12345678-1234-1234-1234-123456789abc';
    $params = [
      'width' => 800,
      'height' => 600,
      'crop' => true,
      'quality' => 85,
    ];

    $signature = $this->service->sign($imageId, $params);
    $isValid = $this->service->verify($imageId, $params, $signature);

    $this->assertTrue($isValid);
  }

  /**
   * @return array<string, array{string, array<string, mixed>}>
   */
  public static function imageParametersProvider(): array {
    return [
      'Full parameters with crop' => [
        '12345678-1234-1234-1234-123456789abc',
        ['width' => 800, 'height' => 600, 'crop' => true],
      ],
      'Full parameters without crop' => [
        '12345678-1234-1234-1234-123456789abc',
        ['width' => 800, 'height' => 600],
      ],
      'Width only' => [
        '87654321-4321-4321-4321-cba987654321',
        ['width' => 400],
      ],
      'Height only' => [
        'abcdef12-3456-7890-abcd-ef1234567890',
        ['height' => 300],
      ],
      'No dimensions' => [
        'fedcba98-7654-3210-fedc-ba9876543210',
        [],
      ],
      'Large dimensions' => [
        '11111111-2222-3333-4444-555555555555',
        ['width' => 4096, 'height' => 4096, 'crop' => true],
      ],
      'Small dimensions' => [
        '99999999-8888-7777-6666-555555555555',
        ['width' => 1, 'height' => 1],
      ],
    ];
  }
}
