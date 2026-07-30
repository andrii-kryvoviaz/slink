<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Settings;

use PHPUnit\Framework\Attributes\Test;
use Slink\Settings\Domain\Enum\BrandingLogoFormat;
use Slink\Shared\Domain\FileSystem\Storage\StorageInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Tests\Integration\Http\HttpTestCase;

final class BrandingLogoTest extends HttpTestCase {
  private function adminToken(): string {
    $userId = $this->createUser('brandingadmin@local.test', 'brandingadmin', self::PASSWORD);
    $this->grantAdmin($userId);

    return $this->login('brandingadmin', self::PASSWORD);
  }

  private function clearLogo(): void {
    /** @var StorageInterface $storage */
    $storage = static::getContainer()->get(StorageInterface::class);
    foreach (BrandingLogoFormat::cases() as $format) {
      $storage->delete($format->fileName());
    }
  }

  private function uploadLogo(UploadedFile $file, string $token): int {
    $this->client->request(
      'POST',
      '/api/settings/customization/logo',
      [],
      ['file' => $file],
      ['HTTP_AUTHORIZATION' => 'Bearer ' . $token],
    );

    return $this->client->getResponse()->getStatusCode();
  }

  private function sampleSvg(): UploadedFile {
    $temp = (string) \tempnam(\sys_get_temp_dir(), 'slink_branding_') . '.svg';

    \file_put_contents($temp, '<?xml version="1.0" encoding="UTF-8"?><svg xmlns="http://www.w3.org/2000/svg" width="1" height="1"></svg>');

    return new UploadedFile($temp, 'logo.svg', 'image/svg+xml', null, true);
  }

  #[Test]
  public function itReturns404WhenNoLogoUploaded(): void {
    $this->clearLogo();

    $this->client->request('GET', '/api/branding/logo');

    self::assertSame(404, $this->client->getResponse()->getStatusCode());
  }

  #[Test]
  public function itRejectsNonAdminUpload(): void {
    $this->createUser('regular@local.test', 'regularuser', self::PASSWORD);
    $token = $this->login('regularuser', self::PASSWORD);

    self::assertSame(403, $this->uploadLogo($this->sampleImage(), $token));
  }

  #[Test]
  public function itRejectsUnsupportedFile(): void {
    $temp = (string) \tempnam(\sys_get_temp_dir(), 'slink_branding_') . '.txt';
    \file_put_contents($temp, 'not an image');
    $file = new UploadedFile($temp, 'note.txt', 'text/plain', null, true);

    self::assertSame(422, $this->uploadLogo($file, $this->adminToken()));
  }

  #[Test]
  public function itUploadsRasterLogoAndServesItAsPng(): void {
    self::assertSame(200, $this->uploadLogo($this->sampleImage(), $this->adminToken()));

    /** @var array{url: string} $payload */
    $payload = \json_decode((string) $this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
    self::assertMatchesRegularExpression('#^/api/branding/logo\?v=[0-9a-f]{8}$#', $payload['url']);

    $this->client->request('GET', $payload['url']);

    $response = $this->client->getResponse();
    self::assertSame(200, $response->getStatusCode());
    self::assertSame('image/png', $response->headers->get('Content-Type'));
    self::assertStringContainsString('immutable', (string) $response->headers->get('Cache-Control'));
    self::assertFalse($response->headers->has('Content-Security-Policy'));
  }

  #[Test]
  public function itReturnsDistinctVersionForDifferentUploads(): void {
    $token = $this->adminToken();

    self::assertSame(200, $this->uploadLogo($this->sampleImage(), $token));
    $firstVersion = $this->uploadedVersion();

    self::assertSame(200, $this->uploadLogo($this->sampleImage(), $token));
    $secondVersion = $this->uploadedVersion();

    self::assertNotSame($firstVersion, $secondVersion);
  }

  #[Test]
  public function itServesUnversionedUrlAsRevalidatableAndVersionedAsImmutable(): void {
    self::assertSame(200, $this->uploadLogo($this->sampleImage(), $this->adminToken()));

    $this->client->request('GET', '/api/branding/logo');
    self::assertStringNotContainsString('immutable', (string) $this->client->getResponse()->headers->get('Cache-Control'));

    $this->client->request('GET', '/api/branding/logo?v=abcdef12');
    self::assertStringContainsString('immutable', (string) $this->client->getResponse()->headers->get('Cache-Control'));
  }

  private function uploadedVersion(): string {
    /** @var array{url: string} $payload */
    $payload = \json_decode((string) $this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

    return $payload['url'];
  }

  #[Test]
  public function itUploadsSvgLogoAndServesItAsIs(): void {
    self::assertSame(200, $this->uploadLogo($this->sampleSvg(), $this->adminToken()));

    $this->client->request('GET', '/api/branding/logo');

    $response = $this->client->getResponse();
    self::assertSame(200, $response->getStatusCode());
    self::assertSame('image/svg+xml', $response->headers->get('Content-Type'));
    self::assertSame("default-src 'none'; style-src 'unsafe-inline'; sandbox", $response->headers->get('Content-Security-Policy'));
  }
}
