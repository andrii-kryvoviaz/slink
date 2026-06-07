<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Image;

use Jcupitt\Vips\Image as VipsImage;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Slink\Shared\Domain\Service\UrlSignatureInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Tests\Integration\Http\HttpTestCase;
use Tests\Support\ImageFormatFixtures;

final class FormatRoundTripTest extends HttpTestCase {
  use ImageFormatFixtures;

  private string $ownerToken = '';
  private string $nonOwnerToken = '';

  /**
   * @return array<int, array{0: string}>
   */
  public static function allFormatProvider(): array {
    return \array_map(static fn(string $format): array => [$format], self::allFormatKeys());
  }

  /**
   * @return array<int, array{0: string}>
   */
  public static function resizableFormatProvider(): array {
    return \array_map(static fn(string $format): array => [$format], self::resizableFormatKeys());
  }

  /**
   * @return array<int, array{0: string}>
   */
  public static function animatedFormatProvider(): array {
    return \array_map(static fn(string $format): array => [$format], self::animatedFormatKeys());
  }

  /**
   * @return array<int, array{0: string}>
   */
  public static function forcedConversionProvider(): array {
    return [['heic'], ['tiff']];
  }

  /**
   * @return array<int, array{0: string}>
   */
  public static function preservedFormatProvider(): array {
    return [['jpeg'], ['png'], ['gif'], ['webp'], ['avif'], ['bmp'], ['svg']];
  }

  /**
   * @return array<int, array{0: string}>
   */
  public static function passThroughFormatProvider(): array {
    return [['svg'], ['ico'], ['tga']];
  }

  private function bootOwner(): void {
    $this->createUser('format-owner@local.test', 'formatowner', self::PASSWORD);
    $this->ownerToken = $this->login('formatowner', self::PASSWORD);
  }

  private function bootActors(): void {
    $this->createUser('format-owner@local.test', 'formatowner', self::PASSWORD);
    $this->createUser('format-other@local.test', 'formatother', self::PASSWORD);

    $this->ownerToken = $this->login('formatowner', self::PASSWORD);
    $this->nonOwnerToken = $this->login('formatother', self::PASSWORD);
  }

  private function signature(): UrlSignatureInterface {
    /** @var UrlSignatureInterface $signature */
    $signature = static::getContainer()->get(UrlSignatureInterface::class);

    return $signature;
  }

  /**
   * @return array{0: int, 1: UploadedFile, 2: string}
   */
  private function tryUpload(string $token, string $format, bool $isPublic, ?string $bytes = null): array {
    try {
      $bytes = $bytes ?? $this->imageBytes($format);
      $file = $this->uploadedImage($format, $bytes);
    } catch (\RuntimeException $exception) {
      self::markTestSkipped(\sprintf('Cannot build %s fixture: %s', $format, $exception->getMessage()));
    }

    $parameters = $isPublic ? ['isPublic' => 'true'] : [];

    $this->client->request(
      'POST',
      '/api/upload',
      $parameters,
      ['image' => $file],
      ['HTTP_AUTHORIZATION' => 'Bearer ' . $token],
    );

    return [$this->client->getResponse()->getStatusCode(), $file, $bytes];
  }

  private function uploadOrSkip(string $token, string $format, bool $isPublic, ?string $bytes = null): string {
    [$status] = $this->tryUpload($token, $format, $isPublic, $bytes);

    self::assertSame(
      Response::HTTP_CREATED,
      $status,
      \sprintf('Upload of %s failed: %s', $format, (string) $this->client->getResponse()->getContent()),
    );

    return $this->extractId((string) $this->client->getResponse()->getContent());
  }

  /**
   * @return array<string, mixed>
   */
  private function imageDetail(string $token, string $imageId): array {
    $status = $this->apiRequest('GET', \sprintf('/api/image/%s/detail', $imageId), $token);
    self::assertSame(
      200,
      $status,
      'Detail request failed: ' . (string) $this->client->getResponse()->getContent(),
    );

    /** @var array{data?: array<string, mixed>} $payload */
    $payload = \json_decode((string) $this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

    return $payload['data'] ?? [];
  }

  private function publishedResizeShare(string $imageId, int $width): string {
    $signature = $this->signature()->sign($imageId, ['width' => $width]);
    $shareId = $this->createImageShareWithWidth($this->ownerToken, $imageId, $width);
    $this->publishShare($this->ownerToken, $shareId);

    return $signature;
  }

  private function createImageShareWithWidth(string $token, string $imageId, int $width): string {
    $this->apiRequest('GET', \sprintf('/api/image/%s/share?width=%d', $imageId, $width), $token);

    $response = $this->client->getResponse();
    self::assertContains(
      $response->getStatusCode(),
      [200, 201],
      'Create share failed: ' . (string) $response->getContent(),
    );

    return $this->extractId((string) $response->getContent());
  }

  private function createImageShareWithWidthAndFormat(string $token, string $imageId, int $width, string $format): string {
    $this->apiRequest('GET', \sprintf('/api/image/%s/share?width=%d&format=%s', $imageId, $width, $format), $token);

    $response = $this->client->getResponse();
    self::assertContains(
      $response->getStatusCode(),
      [200, 201],
      'Create cross-format share failed: ' . (string) $response->getContent(),
    );

    return $this->extractId((string) $response->getContent());
  }

  /**
   * @return array{0: int, 1: string, 2: string}
   */
  private function fetchTransformedAsNonOwner(string $imageId, string $extension, int $width, string $signature): array {
    $path = \sprintf('/api/image/%s.%s?width=%d&s=%s', $imageId, $extension, $width, $signature);

    $this->client->request('GET', $path, [], [], ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->nonOwnerToken]);

    $response = $this->client->getResponse();
    $content = $response->getContent();

    return [
      $response->getStatusCode(),
      (string) $response->headers->get('Content-Type'),
      $content === false ? '' : $content,
    ];
  }

  /**
   * @return array{0: int, 1: string, 2: string}
   */
  private function fetchAsOwner(string $path): array {
    $this->client->request('GET', $path, [], [], ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->ownerToken]);

    $response = $this->client->getResponse();
    $content = $response->getContent();

    return [
      $response->getStatusCode(),
      (string) $response->headers->get('Content-Type'),
      $content === false ? '' : $content,
    ];
  }

  private function decode(string $bytes): VipsImage {
    self::assertNotSame('', $bytes, 'Served image body is empty.');

    return VipsImage::newFromBuffer($bytes);
  }

  #[Test]
  #[DataProvider('allFormatProvider')]
  public function itUploadsEverySupportedFormat(string $format): void {
    $this->bootOwner();

    [$status] = $this->tryUpload($this->ownerToken, $format, false);

    self::assertSame(
      Response::HTTP_CREATED,
      $status,
      \sprintf('Upload of %s failed: %s', $format, (string) $this->client->getResponse()->getContent()),
    );

    $imageId = $this->extractId((string) $this->client->getResponse()->getContent());
    self::assertNotSame('', $imageId, \sprintf('No id returned for %s upload.', $format));
  }

  #[Test]
  #[DataProvider('forcedConversionProvider')]
  public function itForcesHeicAndTiffToJpegOnUpload(string $format): void {
    $this->bootOwner();

    $imageId = $this->uploadOrSkip($this->ownerToken, $format, false);
    $detail = $this->imageDetail($this->ownerToken, $imageId);

    self::assertSame('image/jpeg', $detail['mimeType'] ?? null, \sprintf('%s was not converted to jpeg.', $format));
    self::assertIsString($detail['fileName'] ?? null);
    self::assertStringEndsWith('.jpg', (string) $detail['fileName']);

    $this->client->request('GET', \sprintf('/api/image/%s.jpg', $imageId), [], [], ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->ownerToken]);
    $response = $this->client->getResponse();
    self::assertSame(200, $response->getStatusCode(), (string) $response->getContent());

    $served = $this->decode((string) $response->getContent());
    self::assertSame('jpegload_buffer', $served->get('vips-loader'), \sprintf('Served %s bytes are not jpeg.', $format));
  }

  #[Test]
  #[DataProvider('preservedFormatProvider')]
  public function itStoresOtherFormatsWithoutForcedConversion(string $format): void {
    $this->bootOwner();

    $imageId = $this->uploadOrSkip($this->ownerToken, $format, false);
    $detail = $this->imageDetail($this->ownerToken, $imageId);

    self::assertSame(
      self::formatMimeType($format),
      $detail['mimeType'] ?? null,
      \sprintf('%s was stored with an unexpected mime type.', $format),
    );
  }

  #[Test]
  #[DataProvider('resizableFormatProvider')]
  public function itServesResizableFormatsThroughPublishedShareWithResize(string $format): void {
    $this->setAccessSettings(['requireAuthForMediaShares' => false]);
    $this->bootActors();

    $imageId = $this->uploadOrSkip($this->ownerToken, $format, false);
    $extension = self::formatExtension($format);
    $signature = $this->publishedResizeShare($imageId, 16);

    [$status, $contentType, $body] = $this->fetchTransformedAsNonOwner($imageId, $extension, 16, $signature);

    self::assertSame(200, $status, \sprintf('Non-owner could not read shared %s: %s', $format, $body));

    $decoded = $this->decode($body);
    self::assertLessThanOrEqual(17, $decoded->width, \sprintf('Resize not applied for %s.', $format));
    self::assertGreaterThan(0, $decoded->width);

    if ($format === 'bmp') {
      self::assertSame('image/bmp', $contentType, 'BMP content type changed unexpectedly.');
      self::assertSame(
        'jpegload_buffer',
        $decoded->get('vips-loader'),
        'BMP body codec no longer mismatches the content type; update the documented finding.',
      );

      return;
    }

    self::assertSame(
      self::formatMimeType($format),
      $contentType,
      \sprintf('Content type mismatch for %s.', $format),
    );
  }

  #[Test]
  #[DataProvider('animatedFormatProvider')]
  public function itPreservesAnimationThroughShareResize(string $format): void {
    $this->setAccessSettings(['requireAuthForMediaShares' => false]);
    $this->bootActors();

    $bytes = $this->animatedImageBytes($format, 3);
    $imageId = $this->uploadOrSkip($this->ownerToken, $format, false, $bytes);
    $extension = self::formatExtension($format);
    $signature = $this->publishedResizeShare($imageId, 16);

    [$status, $contentType, $body] = $this->fetchTransformedAsNonOwner($imageId, $extension, 16, $signature);

    self::assertSame(200, $status, \sprintf('Non-owner could not read animated %s: %s', $format, $body));
    self::assertSame(self::formatMimeType($format), $contentType, \sprintf('Content type mismatch for animated %s.', $format));

    $decoded = VipsImage::newFromBuffer($body, '', ['n' => -1]);
    self::assertGreaterThanOrEqual(
      2,
      (int) $decoded->get('n-pages'),
      \sprintf('Animation lost for %s after resize.', $format),
    );
  }

  #[Test]
  #[DataProvider('passThroughFormatProvider')]
  public function itServesSvgIcoTgaAsPassThrough(string $format): void {
    $this->setAccessSettings(['allowUnauthenticatedAccess' => true]);
    $this->bootOwner();

    [$uploadStatus, , $originalBytes] = $this->tryUpload($this->ownerToken, $format, true);

    if ($uploadStatus !== Response::HTTP_CREATED) {
      self::assertSame(
        Response::HTTP_UNPROCESSABLE_ENTITY,
        $uploadStatus,
        \sprintf('%s upload returned an unexpected status: %s', $format, (string) $this->client->getResponse()->getContent()),
      );

      return;
    }

    $imageId = $this->extractId((string) $this->client->getResponse()->getContent());
    $extension = self::formatExtension($format);

    $this->client->request('GET', \sprintf('/api/image/public/%s.%s', $imageId, $extension));
    $response = $this->client->getResponse();

    self::assertSame(200, $response->getStatusCode(), \sprintf('Pass-through serve of %s failed: %s', $format, (string) $response->getContent()));

    $contentType = (string) $response->headers->get('Content-Type');
    $acceptable = $format === 'ico' ? ['image/x-icon', 'image/vnd.microsoft.icon'] : [self::formatMimeType($format)];
    self::assertContains($contentType, $acceptable, \sprintf('Unexpected content type for %s.', $format));

    $body = (string) $response->getContent();

    if ($format === 'svg') {
      self::assertStringContainsString('<svg', $body, 'Served SVG is no longer valid markup.');

      return;
    }

    self::assertSame($originalBytes, $body, \sprintf('%s body was not served byte-identical.', $format));
  }

  #[Test]
  public function itDeniesNonOwnerWithoutPublishedShareThenAllowsAfterPublish(): void {
    $this->setAccessSettings(['requireAuthForMediaShares' => false]);
    $this->bootActors();

    $imageId = $this->uploadOrSkip($this->ownerToken, 'png', false);
    $signature = $this->signature()->sign($imageId, ['width' => 16]);
    $shareId = $this->createImageShareWithWidth($this->ownerToken, $imageId, 16);

    [$deniedStatus] = $this->fetchTransformedAsNonOwner($imageId, 'png', 16, $signature);
    self::assertSame(404, $deniedStatus, 'Non-owner read an unpublished share.');

    $this->publishShare($this->ownerToken, $shareId);

    [$allowedStatus] = $this->fetchTransformedAsNonOwner($imageId, 'png', 16, $signature);
    self::assertSame(200, $allowedStatus, 'Non-owner could not read a published share.');
  }

  #[Test]
  public function itServesFormatConvertedImageThroughPublishedShare(): void {
    $this->setAccessSettings(['requireAuthForMediaShares' => false]);
    $this->bootActors();

    $imageId = $this->uploadOrSkip($this->ownerToken, 'png', false);
    $shareId = $this->createImageShareWithWidthAndFormat($this->ownerToken, $imageId, 16, 'webp');
    $this->publishShare($this->ownerToken, $shareId);

    $signature = $this->signature()->sign($imageId, ['width' => 16]);

    [$status, $contentType, $body] = $this->fetchTransformedAsNonOwner($imageId, 'webp', 16, $signature);

    self::assertSame(200, $status, \sprintf('Non-owner could not read cross-format share: %s', $body));
    self::assertSame('image/webp', $contentType, 'Cross-format share did not return webp content type.');

    $decoded = $this->decode($body);
    self::assertSame(
      'webpload_buffer',
      $decoded->get('vips-loader'),
      'Stored png was not re-encoded to webp through the share.',
    );
    self::assertGreaterThan(0, $decoded->width);
    self::assertLessThanOrEqual(17, $decoded->width, 'Resize not applied through cross-format share.');
  }

  #[Test]
  public function itServesOwnerCroppedAnimatedImageAsWebp(): void {
    $this->bootOwner();

    $bytes = $this->animatedImageBytes('gif', 3, 800, 600);
    $imageId = $this->uploadOrSkip($this->ownerToken, 'gif', false, $bytes);

    [$status, $contentType, $body] = $this->fetchAsOwner(
      \sprintf('/api/image/%s.webp?width=400&height=400&crop=true', $imageId),
    );

    self::assertSame(200, $status, \sprintf('Owner could not read cropped animated image: %s', $body));
    self::assertSame('image/webp', $contentType, 'Owner cropped animated image did not return webp content type.');

    $decoded = VipsImage::newFromBuffer($body, '', ['n' => -1]);
    self::assertGreaterThanOrEqual(
      2,
      (int) $decoded->get('n-pages'),
      'Animation lost through owner cover crop.',
    );
    self::assertSame(400, $decoded->width, 'Cover crop width not applied for owner cropped animated image.');
  }

  #[Test]
  public function itKeepsAnimatedWebpAnimatedThroughWidthAndQualityPreview(): void {
    $this->bootOwner();

    $bytes = $this->animatedImageBytes('webp', 3);
    $imageId = $this->uploadOrSkip($this->ownerToken, 'webp', false, $bytes);

    [$status, $contentType, $body] = $this->fetchAsOwner(
      \sprintf('/api/image/%s.webp?width=1600&quality=82', $imageId),
    );

    self::assertSame(200, $status, \sprintf('Owner could not read animated webp preview: %s', $body));
    self::assertSame('image/webp', $contentType, 'Animated webp preview did not return webp content type.');

    $decoded = VipsImage::newFromBuffer($body, '', ['n' => -1]);
    self::assertGreaterThanOrEqual(
      2,
      (int) $decoded->get('n-pages'),
      'Animation lost through width-only quality preview.',
    );
  }

  #[Test]
  #[DataProvider('animatedFormatProvider')]
  public function itPreservesAnimationForQualityOnlyRequest(string $format): void {
    $this->bootOwner();

    $bytes = $this->animatedImageBytes($format, 3);
    $imageId = $this->uploadOrSkip($this->ownerToken, $format, false, $bytes);
    $extension = self::formatExtension($format);

    [$status, $contentType, $body] = $this->fetchAsOwner(
      \sprintf('/api/image/%s.%s?quality=50', $imageId, $extension),
    );

    self::assertSame(200, $status, \sprintf('Owner could not read quality-only animated %s: %s', $format, $body));
    self::assertSame(
      self::formatMimeType($format),
      $contentType,
      \sprintf('Quality-only request changed content type for animated %s.', $format),
    );

    $decoded = VipsImage::newFromBuffer($body, '', ['n' => -1]);
    self::assertGreaterThanOrEqual(
      2,
      (int) $decoded->get('n-pages'),
      \sprintf('Animation lost through quality-only request for %s.', $format),
    );
  }
}
