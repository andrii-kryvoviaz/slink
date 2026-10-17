<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Image;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\Integration\Http\HttpTestCase;

final class UploadSuccessTest extends HttpTestCase {
  private string $ownerToken = '';

  private function bootOwner(): void {
    $this->createUser('upload-owner@local.test', 'uploadowner', self::PASSWORD);
    $this->ownerToken = $this->login('uploadowner', self::PASSWORD);
  }

  /**
   * @return array<string, mixed>
   */
  private function getImageDetail(string $token, string $imageId): array {
    $status = $this->apiRequest('GET', \sprintf('/api/image/%s/detail', $imageId), $token);
    self::assertSame(200, $status, 'Detail request failed: ' . (string) $this->client->getResponse()->getContent());

    /** @var array{data?: array<string, mixed>} $payload */
    $payload = \json_decode((string) $this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

    return $payload['data'] ?? [];
  }

  /**
   * @return array<string, array{0: string}>
   */
  public static function falseSpellingProvider(): array {
    return [
      'false' => ['false'],
      'no' => ['no'],
      'off' => ['off'],
      'zero' => ['0'],
      'empty' => [''],
    ];
  }

  /**
   * @return array<string, array{0: string}>
   */
  public static function trueSpellingProvider(): array {
    return [
      'true' => ['true'],
      'one' => ['1'],
      'on' => ['on'],
      'yes' => ['yes'],
    ];
  }

  private function uploadWithVisibility(string $isPublic): string {
    $status = $this->uploadImageWithFields($this->ownerToken, ['isPublic' => $isPublic]);
    self::assertSame(201, $status, 'Upload failed: ' . (string) $this->client->getResponse()->getContent());

    $imageId = $this->extractId((string) $this->client->getResponse()->getContent());
    self::assertNotSame('', $imageId);

    return $imageId;
  }

  #[Test]
  public function ownerUploadReturnsCreatedWithIdAndExposesDetail(): void {
    $this->bootOwner();

    $this->client->request(
      'POST',
      '/api/upload',
      [],
      ['image' => $this->sampleImage()],
      ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->ownerToken],
    );

    $response = $this->client->getResponse();
    self::assertSame(201, $response->getStatusCode(), 'Upload failed: ' . (string) $response->getContent());

    $imageId = $this->extractId((string) $response->getContent());
    self::assertNotSame('', $imageId);

    $detail = $this->getImageDetail($this->ownerToken, $imageId);

    self::assertSame($imageId, $detail['id'] ?? null);
    self::assertFalse($detail['isPublic'] ?? null);
    self::assertSame('image/png', $detail['mimeType'] ?? null);
    self::assertIsString($detail['fileName'] ?? null);
    self::assertNotSame('', $detail['fileName']);
  }

  #[Test]
  public function ownerUploadWithIsPublicStoresPublicImage(): void {
    $this->bootOwner();

    $imageId = $this->uploadImage($this->ownerToken, true);
    self::assertNotSame('', $imageId);

    $detail = $this->getImageDetail($this->ownerToken, $imageId);

    self::assertSame($imageId, $detail['id'] ?? null);
    self::assertTrue($detail['isPublic'] ?? null);
  }

  #[Test]
  #[Group('sc-994')]
  #[DataProvider('falseSpellingProvider')]
  public function ownerUploadWithFalseIsPublicSpellingStoresPrivateImage(string $isPublic): void {
    $this->setAccessSettings(['allowUnauthenticatedAccess' => true]);
    $this->bootOwner();

    $imageId = $this->uploadWithVisibility($isPublic);

    self::assertFalse($this->getImageDetail($this->ownerToken, $imageId)['isPublic'] ?? null);
    self::assertSame(404, $this->apiRequest('GET', \sprintf('/api/image/public/%s.png', $imageId)));
  }

  #[Test]
  #[Group('sc-994')]
  #[DataProvider('trueSpellingProvider')]
  public function ownerUploadWithTrueIsPublicSpellingStoresPublicImage(string $isPublic): void {
    $this->setAccessSettings(['allowUnauthenticatedAccess' => true]);
    $this->bootOwner();

    $imageId = $this->uploadWithVisibility($isPublic);

    self::assertTrue($this->getImageDetail($this->ownerToken, $imageId)['isPublic'] ?? null);
    self::assertSame(200, $this->apiRequest('GET', \sprintf('/api/image/public/%s.png', $imageId)));
  }
}
