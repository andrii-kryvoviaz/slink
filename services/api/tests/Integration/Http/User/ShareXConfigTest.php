<?php

declare(strict_types=1);

namespace Tests\Integration\Http\User;

use PHPUnit\Framework\Attributes\Test;
use Tests\Integration\Http\HttpTestCase;

final class ShareXConfigTest extends HttpTestCase {
  private const string BASE_URL = 'http://localhost';

  /**
   * @param array<string, string> $body
   */
  private function requestConfig(?string $token, array $body): int {
    $headers = ['CONTENT_TYPE' => 'application/json'];

    if ($token !== null) {
      $headers['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;
    }

    $this->client->request(
      'POST',
      '/api/user/sharex-config',
      [],
      [],
      $headers,
      \json_encode($body, JSON_THROW_ON_ERROR),
    );

    return $this->client->getResponse()->getStatusCode();
  }

  private function bootOwner(): string {
    $this->createUser('sharex-config-owner@local.test', 'sharexconfigowner', self::PASSWORD);

    return $this->login('sharexconfigowner', self::PASSWORD);
  }

  #[Test]
  public function anonymousRequestIsRejected(): void {
    self::assertSame(401, $this->requestConfig(null, ['apiKey' => 'sk_anonymous']));
  }

  #[Test]
  public function missingApiKeyFailsValidation(): void {
    $token = $this->bootOwner();

    self::assertSame(422, $this->requestConfig($token, []));
  }

  #[Test]
  public function overlongApiKeyFailsValidation(): void {
    $token = $this->bootOwner();

    self::assertSame(422, $this->requestConfig($token, ['apiKey' => \str_repeat('a', 256)]));
  }

  #[Test]
  public function overlongBaseUrlFailsValidation(): void {
    $token = $this->bootOwner();
    $apiKey = $this->createApiKey($token);

    self::assertSame(422, $this->requestConfig($token, [
      'apiKey' => $apiKey['key'],
      'baseUrl' => \str_repeat('b', 256),
    ]));
  }

  #[Test]
  public function unknownApiKeyIsNotFound(): void {
    $token = $this->bootOwner();

    self::assertSame(404, $this->requestConfig($token, ['apiKey' => 'sk_unknown_key']));
  }

  #[Test]
  public function apiKeyOfAnotherUserIsNotFound(): void {
    $ownerToken = $this->bootOwner();
    $ownerKey = $this->createApiKey($ownerToken);

    $this->createUser('sharex-config-other@local.test', 'sharexconfigother', self::PASSWORD);
    $otherToken = $this->login('sharexconfigother', self::PASSWORD);

    self::assertSame(404, $this->requestConfig($otherToken, ['apiKey' => $ownerKey['key']]));
  }

  #[Test]
  public function expiredApiKeyIsNotFound(): void {
    $token = $this->bootOwner();
    $apiKey = $this->createApiKey($token, $this->futureIso(1));

    \sleep(2);

    self::assertSame(404, $this->requestConfig($token, ['apiKey' => $apiKey['key']]));
  }

  #[Test]
  public function generatedConfigMatchesShareXContract(): void {
    $token = $this->bootOwner();
    $apiKey = $this->createApiKey($token);

    $status = $this->requestConfig($token, ['apiKey' => $apiKey['key'], 'baseUrl' => self::BASE_URL]);
    self::assertSame(200, $status, 'Config request failed: ' . (string) $this->client->getResponse()->getContent());

    /** @var array<string, mixed> $config */
    $config = \json_decode((string) $this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

    self::assertSame([
      'Version' => '14.1.0',
      'Name' => 'Slink Image Upload',
      'DestinationType' => 'ImageUploader',
      'RequestMethod' => 'POST',
      'RequestURL' => self::BASE_URL . '/api/external/upload',
      'Headers' => [
        'Authorization' => 'Bearer ' . $apiKey['key'],
        'Origin' => self::BASE_URL,
      ],
      'Body' => 'MultipartFormData',
      'FileFormName' => 'image',
      'URL' => self::BASE_URL . '/{json:url}',
      'ThumbnailURL' => self::BASE_URL . '/{json:thumbnailUrl}',
    ], $config);
  }
}
