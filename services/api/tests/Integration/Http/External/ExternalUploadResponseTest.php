<?php

declare(strict_types=1);

namespace Tests\Integration\Http\External;

use PHPUnit\Framework\Attributes\Test;
use Ramsey\Uuid\Uuid;
use Tests\Integration\Http\HttpTestCase;

final class ExternalUploadResponseTest extends HttpTestCase {
  private function bootOwnerApiKey(): string {
    $this->createUser('external-upload-owner@local.test', 'externaluploadowner', self::PASSWORD);
    $token = $this->login('externaluploadowner', self::PASSWORD);

    return $this->createApiKey($token)['key'];
  }

  /**
   * @return array<string, mixed>
   */
  private function externalUpload(string $apiKey): array {
    $this->client->request(
      'POST',
      '/api/external/upload',
      [],
      ['image' => $this->sampleImage()],
      ['HTTP_AUTHORIZATION' => 'Bearer ' . $apiKey],
    );

    $response = $this->client->getResponse();
    self::assertSame(201, $response->getStatusCode(), 'External upload failed: ' . (string) $response->getContent());

    /** @var array<string, mixed> $payload */
    $payload = \json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);

    return $payload;
  }

  #[Test]
  public function responseIsUnwrappedWithExactFieldNames(): void {
    $apiKey = $this->bootOwnerApiKey();

    $payload = $this->externalUpload($apiKey);

    self::assertSame(['url', 'thumbnailUrl', 'id'], \array_keys($payload));
    self::assertIsString($payload['id']);
    self::assertTrue(Uuid::isValid($payload['id']), 'The id field must be the image UUID.');
  }

  #[Test]
  public function urlsResolveToShortCodesWhenShorteningIsEnabled(): void {
    $apiKey = $this->bootOwnerApiKey();

    $payload = $this->externalUpload($apiKey);

    self::assertIsString($payload['url']);
    self::assertIsString($payload['thumbnailUrl']);
    self::assertMatchesRegularExpression('#^i/[0-9A-Za-z]+$#', $payload['url']);
    self::assertMatchesRegularExpression('#^i/[0-9A-Za-z]+$#', $payload['thumbnailUrl']);
    self::assertNotSame($payload['url'], $payload['thumbnailUrl']);
  }

  #[Test]
  public function urlsExposeTargetPathsWhenShorteningIsDisabled(): void {
    $this->saveSettings('share', ['enableUrlShortening' => false]);
    $apiKey = $this->bootOwnerApiKey();

    $payload = $this->externalUpload($apiKey);

    self::assertIsString($payload['url']);
    self::assertIsString($payload['thumbnailUrl']);
    self::assertMatchesRegularExpression('#^image/[^/?]+\.png$#', $payload['url']);
    self::assertMatchesRegularExpression(
      '#^image/[^/?]+\.png\?width=300&height=300&crop=1&s=[0-9a-f]{64}$#',
      $payload['thumbnailUrl'],
    );
    self::assertStringStartsWith($payload['url'] . '?', $payload['thumbnailUrl']);
  }
}
