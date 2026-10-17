<?php

declare(strict_types=1);

namespace Tests\Integration\Http\External;

use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\Integration\Http\HttpTestCase;
use Tests\Integration\Http\Image\UploadSuccessTest;

final class ExternalUploadVisibilityTest extends HttpTestCase {
  private const string ENDPOINT = '/api/external/upload';

  private string $ownerToken = '';
  private string $apiKey = '';

  protected function setUp(): void {
    parent::setUp();

    $this->setAccessSettings(['allowUnauthenticatedAccess' => true]);
    $this->createUser('external-visibility-owner@local.test', 'externalvisibilityowner', self::PASSWORD);
    $this->ownerToken = $this->login('externalvisibilityowner', self::PASSWORD);
    $this->apiKey = $this->createApiKey($this->ownerToken)['key'];
  }

  /**
   * @param array<string, string> $fields
   */
  private function externalUpload(array $fields): string {
    $status = $this->uploadImageWithFields($this->apiKey, $fields, self::ENDPOINT);
    self::assertSame(201, $status, 'External upload failed: ' . (string) $this->client->getResponse()->getContent());

    $imageId = $this->extractId((string) $this->client->getResponse()->getContent());
    self::assertNotSame('', $imageId);

    return $imageId;
  }

  private function ownerReportedIsPublic(string $imageId): mixed {
    $status = $this->apiRequest('GET', \sprintf('/api/image/%s/detail', $imageId), $this->ownerToken);
    self::assertSame(200, $status, 'Detail request failed: ' . (string) $this->client->getResponse()->getContent());

    /** @var array{data?: array<string, mixed>} $payload */
    $payload = \json_decode((string) $this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

    return $payload['data']['isPublic'] ?? null;
  }

  private function anonymousContentStatus(string $imageId): int {
    return $this->apiRequest('GET', \sprintf('/api/image/public/%s.png', $imageId));
  }

  #[Test]
  #[Group('sc-994')]
  #[DataProviderExternal(UploadSuccessTest::class, 'falseSpellingProvider')]
  public function externalUploadWithFalseIsPublicSpellingStoresPrivateImage(string $isPublic): void {
    $imageId = $this->externalUpload(['isPublic' => $isPublic]);

    self::assertFalse($this->ownerReportedIsPublic($imageId));
    self::assertSame(404, $this->anonymousContentStatus($imageId));
  }

  #[Test]
  #[Group('sc-994')]
  #[DataProviderExternal(UploadSuccessTest::class, 'trueSpellingProvider')]
  public function externalUploadWithTrueIsPublicSpellingStoresPublicImage(string $isPublic): void {
    $imageId = $this->externalUpload(['isPublic' => $isPublic]);

    self::assertTrue($this->ownerReportedIsPublic($imageId));
    self::assertSame(200, $this->anonymousContentStatus($imageId));
  }

  #[Test]
  #[Group('sc-994')]
  public function externalUploadWithoutIsPublicStoresPrivateImage(): void {
    $imageId = $this->externalUpload([]);

    self::assertFalse($this->ownerReportedIsPublic($imageId));
    self::assertSame(404, $this->anonymousContentStatus($imageId));
  }
}
