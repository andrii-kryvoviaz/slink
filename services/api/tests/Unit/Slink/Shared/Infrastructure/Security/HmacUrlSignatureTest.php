<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Shared\Infrastructure\Security;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Infrastructure\Security\HmacUrlSignature;

final class HmacUrlSignatureTest extends TestCase {
  private HmacUrlSignature $service;
  private string $secret = 'test-secret-key';

  protected function setUp(): void {
    $this->service = new HmacUrlSignature($this->secret);
  }

  #[Test]
  public function itGeneratesValidSignature(): void {
    $scope = '12345678-1234-1234-1234-123456789abc';
    $payload = [
      'width' => 800,
      'height' => 600,
      'crop' => true,
    ];

    $signature = $this->service->sign($scope, $payload);

    $this->assertEquals(64, strlen($signature));
    $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $signature);
  }

  #[Test]
  public function itVerifiesValidSignature(): void {
    $scope = '12345678-1234-1234-1234-123456789abc';
    $payload = [
      'width' => 800,
      'height' => 600,
      'crop' => true,
    ];

    $signature = $this->service->sign($scope, $payload);
    $isValid = $this->service->verify($scope, $payload, $signature);

    $this->assertTrue($isValid);
  }

  #[Test]
  public function itRejectsInvalidSignature(): void {
    $scope = '12345678-1234-1234-1234-123456789abc';
    $payload = [
      'width' => 800,
      'height' => 600,
      'crop' => true,
    ];

    $invalidSignature = 'invalid_signature_that_should_not_match';
    $isValid = $this->service->verify($scope, $payload, $invalidSignature);

    $this->assertFalse($isValid);
  }

  #[Test]
  public function itRejectsSignatureWithDifferentParameters(): void {
    $scope = '12345678-1234-1234-1234-123456789abc';
    $payload1 = [
      'width' => 800,
      'height' => 600,
      'crop' => true,
    ];
    $payload2 = [
      'width' => 400,
      'height' => 300,
      'crop' => true,
    ];

    $signature = $this->service->sign($scope, $payload1);
    $isValid = $this->service->verify($scope, $payload2, $signature);

    $this->assertFalse($isValid);
  }

  #[Test]
  public function itRejectsSignatureWithDifferentScope(): void {
    $scope = '12345678-1234-1234-1234-123456789abc';
    $differentScope = '87654321-4321-4321-4321-cba987654321';
    $payload = [
      'width' => 800,
      'height' => 600,
      'crop' => true,
    ];

    $signature = $this->service->sign($scope, $payload);
    $isValid = $this->service->verify($differentScope, $payload, $signature);

    $this->assertFalse($isValid);
  }

  #[Test]
  public function itHandlesEmptyPayload(): void {
    $scope = '12345678-1234-1234-1234-123456789abc';
    $payload = [];

    $signature = $this->service->sign($scope, $payload);
    $isValid = $this->service->verify($scope, $payload, $signature);

    $this->assertTrue($isValid);
  }

  #[Test]
  public function itHandlesPartialDimensions(): void {
    $scope = '12345678-1234-1234-1234-123456789abc';

    $payloadWidthOnly = ['width' => 800];
    $signatureWidthOnly = $this->service->sign($scope, $payloadWidthOnly);
    $this->assertTrue($this->service->verify($scope, $payloadWidthOnly, $signatureWidthOnly));

    $payloadHeightOnly = ['height' => 600];
    $signatureHeightOnly = $this->service->sign($scope, $payloadHeightOnly);
    $this->assertTrue($this->service->verify($scope, $payloadHeightOnly, $signatureHeightOnly));
  }

  #[Test]
  public function itDifferentiatesCropParameter(): void {
    $scope = '12345678-1234-1234-1234-123456789abc';
    $payloadCrop = ['width' => 800, 'height' => 600, 'crop' => true];
    $payloadNoCrop = ['width' => 800, 'height' => 600];

    $signatureCrop = $this->service->sign($scope, $payloadCrop);
    $signatureNoCrop = $this->service->sign($scope, $payloadNoCrop);

    $this->assertNotEquals($signatureCrop, $signatureNoCrop);
    $this->assertFalse($this->service->verify($scope, $payloadNoCrop, $signatureCrop));
    $this->assertFalse($this->service->verify($scope, $payloadCrop, $signatureNoCrop));
  }

  /**
   * @param array<string, mixed> $payload
   */
  #[Test]
  #[DataProvider('payloadProvider')]
  public function itGeneratesConsistentSignatures(
    string $scope,
    array $payload
  ): void {
    $signature1 = $this->service->sign($scope, $payload);
    $signature2 = $this->service->sign($scope, $payload);

    $this->assertEquals($signature1, $signature2);
  }

  #[Test]
  public function itProtectsAgainstTimingAttacks(): void {
    $scope = '12345678-1234-1234-1234-123456789abc';
    $payload = ['width' => 800, 'height' => 600, 'crop' => true];

    $correctSignature = $this->service->sign($scope, $payload);
    $almostCorrectSignature = substr($correctSignature, 0, -1) . 'f';

    $startCorrect = microtime(true);
    $this->service->verify($scope, $payload, $correctSignature);
    $timeCorrect = microtime(true) - $startCorrect;

    $startWrong = microtime(true);
    $this->service->verify($scope, $payload, $almostCorrectSignature);
    $timeWrong = microtime(true) - $startWrong;

    $timeDifference = abs($timeCorrect - $timeWrong);
    $this->assertLessThan(0.0001, $timeDifference, 'Timing attack vulnerability detected');
  }

  #[Test]
  public function itProducesStableWireFormatForTransforms(): void {
    $scope = '12345678-1234-1234-1234-123456789abc';
    $payload = ['width' => 800, 'height' => 600, 'crop' => true];

    $signature = $this->service->sign($scope, $payload);

    $this->assertSame(
      '0cb51073e75b875d5bc0fa193a9c92552459cf535a8d865f1265bddb5f4dcdcf',
      $signature,
      'Backwards-compatibility vector. If this changes, every previously minted share URL with transforms breaks.',
    );
  }

  #[Test]
  public function itProducesStableWireFormatForCollectionScope(): void {
    $scope = '12345678-1234-1234-1234-123456789abc';
    $payload = ['collection' => 'col-abc'];

    $signature = $this->service->sign($scope, $payload);

    $this->assertSame(
      '4af2943877c4a37a1b6fcec14694f52fee4a4be1a901b08019dd620648b692b1',
      $signature,
      'Backwards-compatibility vector. If this changes, every previously minted collection share URL breaks.',
    );
  }

  #[Test]
  public function itSortsPayloadForConsistentSignatures(): void {
    $scope = '12345678-1234-1234-1234-123456789abc';
    $payload1 = ['width' => 800, 'height' => 600, 'crop' => true];
    $payload2 = ['crop' => true, 'height' => 600, 'width' => 800];

    $signature1 = $this->service->sign($scope, $payload1);
    $signature2 = $this->service->sign($scope, $payload2);

    $this->assertEquals($signature1, $signature2);
  }

  #[Test]
  public function itSupportsAdditionalPayload(): void {
    $scope = '12345678-1234-1234-1234-123456789abc';
    $payload = [
      'width' => 800,
      'height' => 600,
      'crop' => true,
      'quality' => 85,
    ];

    $signature = $this->service->sign($scope, $payload);
    $isValid = $this->service->verify($scope, $payload, $signature);

    $this->assertTrue($isValid);
  }

  /**
   * @return array<string, array{string, array<string, mixed>}>
   */
  public static function payloadProvider(): array {
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
