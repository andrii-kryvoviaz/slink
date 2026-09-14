<?php

declare(strict_types=1);

namespace Tests\Integration\Http\User;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\Integration\Http\HttpTestCase;

final class UpdateUserPreferencesTest extends HttpTestCase {
  private const array SEED = [
    'license.default' => 'cc-by',
    'navigation.landingPage' => 'upload',
    'image.defaultVisibility' => 'public',
    'image.stripExifMetadataOverride' => 'strip',
    'image.externalUploadAutoPublish' => true,
    'display.language' => 'de',
    'display.theme' => 'nord',
  ];

  private string $ownerToken = '';

  protected function setUp(): void {
    parent::setUp();

    $this->createUser('owner@local.test', 'owneruser', self::PASSWORD);
    $this->ownerToken = $this->login('owneruser', self::PASSWORD);
  }

  /**
   * @param array<string, mixed> $body
   */
  private function patch(array $body): void {
    $status = $this->apiRequest(
      'PATCH',
      '/api/user/preferences',
      $this->ownerToken,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode($body, JSON_THROW_ON_ERROR),
    );

    self::assertContains(
      $status,
      [200, 204],
      'Update preferences failed: ' . (string) $this->client->getResponse()->getContent(),
    );
  }

  /**
   * @return array<string, mixed>
   */
  private function read(string $path): array {
    $status = $this->apiRequest('GET', $path, $this->ownerToken);
    self::assertSame(200, $status, 'Read failed: ' . (string) $this->client->getResponse()->getContent());

    /** @var array<string, mixed> $data */
    $data = $this->responsePayload()['data'] ?? [];

    return $data;
  }

  #[Test]
  public function everyStoredPreferenceRoundTrips(): void {
    $this->patch(self::SEED);

    $preferences = $this->read('/api/user/preferences');

    foreach (self::SEED as $key => $value) {
      self::assertSame($value, $preferences[$key] ?? null, $key);
    }
  }

  #[Test]
  public function aSingleKeyPatchLeavesTheOtherKeysUnchanged(): void {
    $this->patch(self::SEED);
    $this->patch(['display.theme' => 'catppuccin']);

    $preferences = $this->read('/api/user/preferences');

    self::assertSame('catppuccin', $preferences['display.theme'] ?? null);

    foreach (self::SEED as $key => $value) {
      if ($key === 'display.theme') {
        continue;
      }

      self::assertSame($value, $preferences[$key] ?? null, $key);
    }
  }

  #[Test]
  public function aNullLicenseLeavesTheStoredLicenseUnchanged(): void {
    $this->patch(['license.default' => 'cc-by']);
    $this->patch(['license.default' => null]);

    $preferences = $this->read('/api/user/preferences');

    self::assertSame('cc-by', $preferences['license.default'] ?? null);
  }

  #[Test]
  public function aStringBooleanIsStoredAsABoolean(): void {
    $this->patch(['image.externalUploadAutoPublish' => 'true']);

    $preferences = $this->read('/api/user/preferences');
    self::assertSame(true, $preferences['image.externalUploadAutoPublish'] ?? null);

    $this->patch(['image.externalUploadAutoPublish' => 'false']);

    $preferences = $this->read('/api/user/preferences');
    self::assertSame(false, $preferences['image.externalUploadAutoPublish'] ?? null);
  }

  #[Test]
  #[TestWith(['display.theme', 'nord', 'solarized'])]
  #[TestWith(['license.default', 'cc-by', 'none'])]
  public function anInvalidValueIsRejectedAndNothingChanges(string $key, string $stored, string $invalid): void {
    $this->patch([$key => $stored]);

    self::assertSame(422, $this->apiRequest(
      'PATCH',
      '/api/user/preferences',
      $this->ownerToken,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode([$key => $invalid], JSON_THROW_ON_ERROR),
    ));

    $preferences = $this->read('/api/user/preferences');
    self::assertSame($stored, $preferences[$key] ?? null);
  }

  #[Test]
  #[TestWith([true])]
  #[TestWith(['true'])]
  public function theSyncTriggerLicensesExistingImagesAndIsNotStored(bool|string $syncToImages): void {
    $this->saveSettings('image', ['maxSize' => '5M', 'enableLicensing' => true]);
    $imageId = $this->uploadImage($this->ownerToken, false);

    $before = $this->read(\sprintf('/api/image/%s/detail', $imageId));
    self::assertNotSame('cc-by', $before['license'] ?? null);

    $this->patch(['license.default' => 'cc-by', 'license.syncToImages' => $syncToImages]);

    $detail = $this->read(\sprintf('/api/image/%s/detail', $imageId));
    self::assertSame('cc-by', $detail['license'] ?? null);

    $preferences = $this->read('/api/user/preferences');
    self::assertArrayNotHasKey('license.syncToImages', $preferences);
    self::assertSame('cc-by', $preferences['license.default'] ?? null);
  }
}
