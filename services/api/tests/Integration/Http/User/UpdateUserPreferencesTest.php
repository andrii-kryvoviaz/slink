<?php

declare(strict_types=1);

namespace Tests\Integration\Http\User;

use PHPUnit\Framework\Attributes\Test;
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
  private function patch(array $body): int {
    return $this->apiRequest(
      'PATCH',
      '/api/user/preferences',
      $this->ownerToken,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode($body, JSON_THROW_ON_ERROR),
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
    self::assertContains(
      $this->patch(self::SEED),
      [200, 204],
      'Update preferences failed: ' . (string) $this->client->getResponse()->getContent(),
    );

    $preferences = $this->read('/api/user/preferences');

    foreach (self::SEED as $key => $value) {
      self::assertSame($value, $preferences[$key] ?? null, $key);
    }
  }

  #[Test]
  public function aSingleKeyPatchLeavesTheOtherKeysUnchanged(): void {
    self::assertContains(
      $this->patch(self::SEED),
      [200, 204],
      'Seed preferences failed: ' . (string) $this->client->getResponse()->getContent(),
    );
    self::assertContains(
      $this->patch(['display.theme' => 'catppuccin']),
      [200, 204],
      'Update theme failed: ' . (string) $this->client->getResponse()->getContent(),
    );

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
  public function aNullLicenseClearsTheStoredLicense(): void {
    self::assertContains(
      $this->patch(['license.default' => 'cc-by']),
      [200, 204],
      'Seed license failed: ' . (string) $this->client->getResponse()->getContent(),
    );
    self::assertContains(
      $this->patch(['license.default' => null]),
      [200, 204],
      'Null license patch failed: ' . (string) $this->client->getResponse()->getContent(),
    );

    $preferences = $this->read('/api/user/preferences');

    self::assertNull($preferences['license.default'] ?? null);
  }

  #[Test]
  public function aStringBooleanIsStoredAsABoolean(): void {
    self::assertContains(
      $this->patch(['image.externalUploadAutoPublish' => 'true']),
      [200, 204],
      'True string patch failed: ' . (string) $this->client->getResponse()->getContent(),
    );

    $preferences = $this->read('/api/user/preferences');
    self::assertSame(true, $preferences['image.externalUploadAutoPublish'] ?? null);

    self::assertContains(
      $this->patch(['image.externalUploadAutoPublish' => 'false']),
      [200, 204],
      'False string patch failed: ' . (string) $this->client->getResponse()->getContent(),
    );

    $preferences = $this->read('/api/user/preferences');
    self::assertSame(false, $preferences['image.externalUploadAutoPublish'] ?? null);
  }

  #[Test]
  public function anInvalidValueIsRejectedAndNothingChanges(): void {
    self::assertContains(
      $this->patch(['display.theme' => 'nord']),
      [200, 204],
      'Seed theme failed: ' . (string) $this->client->getResponse()->getContent(),
    );

    self::assertSame(422, $this->patch(['display.theme' => 'solarized']));

    $preferences = $this->read('/api/user/preferences');
    self::assertSame('nord', $preferences['display.theme'] ?? null);
  }

  #[Test]
  public function theSyncTriggerLicensesExistingImagesAndIsNotStored(): void {
    $this->saveSettings('image', ['maxSize' => '5M', 'enableLicensing' => true]);
    $imageId = $this->uploadImage($this->ownerToken, false);

    $before = $this->read(\sprintf('/api/image/%s/detail', $imageId));
    self::assertNotSame('cc-by', $before['license'] ?? null);

    self::assertContains(
      $this->patch(['license.default' => 'cc-by', 'license.syncToImages' => true]),
      [200, 204],
      'Sync license patch failed: ' . (string) $this->client->getResponse()->getContent(),
    );

    $detail = $this->read(\sprintf('/api/image/%s/detail', $imageId));
    self::assertSame('cc-by', $detail['license'] ?? null);

    $preferences = $this->read('/api/user/preferences');
    self::assertArrayNotHasKey('license.syncToImages', $preferences);
    self::assertSame('cc-by', $preferences['license.default'] ?? null);
  }

  #[Test]
  public function aStringSyncTriggerLicensesExistingImages(): void {
    $this->saveSettings('image', ['maxSize' => '5M', 'enableLicensing' => true]);
    $imageId = $this->uploadImage($this->ownerToken, false);

    $before = $this->read(\sprintf('/api/image/%s/detail', $imageId));
    self::assertNotSame('cc-by', $before['license'] ?? null);

    self::assertContains(
      $this->patch(['license.default' => 'cc-by', 'license.syncToImages' => 'true']),
      [200, 204],
      'Sync license patch failed: ' . (string) $this->client->getResponse()->getContent(),
    );

    $detail = $this->read(\sprintf('/api/image/%s/detail', $imageId));
    self::assertSame('cc-by', $detail['license'] ?? null);

    $preferences = $this->read('/api/user/preferences');
    self::assertArrayNotHasKey('license.syncToImages', $preferences);
    self::assertSame('cc-by', $preferences['license.default'] ?? null);
  }
}
