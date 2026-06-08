<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Image;

use Jcupitt\Vips\Image as VipsImage;
use PHPUnit\Framework\Attributes\Test;
use Slink\Shared\Domain\Service\UrlSignatureInterface;
use Tests\Integration\Http\HttpTestCase;
use Tests\Support\ImageFormatFixtures;

final class TransformSignatureTest extends HttpTestCase {
  use ImageFormatFixtures;

  private const int SOURCE_SIZE = 64;
  private const int TARGET_WIDTH = 32;

  private string $token = '';
  private string $nonOwnerToken = '';

  private function bootUser(): void {
    $this->createUser('transform-owner@local.test', 'transformowner', self::PASSWORD);
    $this->token = $this->login('transformowner', self::PASSWORD);
  }

  private function bootActors(): void {
    $this->createUser('transform-owner@local.test', 'transformowner', self::PASSWORD);
    $this->createUser('transform-other@local.test', 'transformother', self::PASSWORD);

    $this->token = $this->login('transformowner', self::PASSWORD);
    $this->nonOwnerToken = $this->login('transformother', self::PASSWORD);
  }

  private function signature(): UrlSignatureInterface {
    /** @var UrlSignatureInterface $signature */
    $signature = static::getContainer()->get(UrlSignatureInterface::class);

    return $signature;
  }

  private function fetchContentWithToken(string $path, ?string $token): string {
    $headers = $token === null ? [] : ['HTTP_AUTHORIZATION' => 'Bearer ' . $token];
    $this->client->request('GET', $path, [], [], $headers);

    $response = $this->client->getResponse();
    self::assertSame(200, $response->getStatusCode(), 'Image content request failed: ' . (string) $response->getContent());

    $content = $response->getContent();

    return $content === false ? '' : $content;
  }

  private function uploadSizedPng(string $token): string {
    $bytes = $this->imageBytes('png', self::SOURCE_SIZE, self::SOURCE_SIZE);
    $file = $this->uploadedImage('png', $bytes);

    $this->client->request(
      'POST',
      '/api/upload',
      [],
      ['image' => $file],
      ['HTTP_AUTHORIZATION' => 'Bearer ' . $token],
    );

    $response = $this->client->getResponse();
    self::assertContains(
      $response->getStatusCode(),
      [200, 201],
      'Upload failed: ' . (string) $response->getContent(),
    );

    return $this->extractId((string) $response->getContent());
  }

  private function publishImageShareWithWidth(string $imageId, int $width): void {
    $this->apiRequest('GET', \sprintf('/api/image/%s/share?width=%d', $imageId, $width), $this->token);

    $response = $this->client->getResponse();
    self::assertContains(
      $response->getStatusCode(),
      [200, 201],
      'Create share failed: ' . (string) $response->getContent(),
    );

    $this->publishShare($this->token, $this->extractId((string) $response->getContent()));
  }

  private function decodedWidth(string $bytes): int {
    self::assertNotSame('', $bytes, 'Served image body is empty.');

    return VipsImage::newFromBuffer($bytes)->width;
  }

  #[Test]
  public function signedTransformIsApplied(): void {
    $this->bootUser();
    $image = $this->uploadSizedPng($this->token);

    $valid = $this->signature()->sign($image, ['width' => self::TARGET_WIDTH]);
    $served = $this->fetchContentWithToken(
      \sprintf('/api/image/%s.png?width=%d&s=%s', $image, self::TARGET_WIDTH, $valid),
      $this->token,
    );

    self::assertSame(self::TARGET_WIDTH, $this->decodedWidth($served));
  }

  #[Test]
  public function ownerUnsignedTransformIsApplied(): void {
    $this->bootUser();
    $image = $this->uploadSizedPng($this->token);

    $served = $this->fetchContentWithToken(\sprintf('/api/image/%s.png?width=%d', $image, self::TARGET_WIDTH), $this->token);

    self::assertSame(self::TARGET_WIDTH, $this->decodedWidth($served), 'Owner unsigned transform was not applied.');
  }

  #[Test]
  public function nonOwnerUnsignedTransformIsDropped(): void {
    $this->setAccessSettings(['requireAuthForMediaShares' => false]);
    $this->bootActors();
    $image = $this->uploadSizedPng($this->token);
    $this->publishImageShareWithWidth($image, self::TARGET_WIDTH);

    $served = $this->fetchContentWithToken(\sprintf('/api/image/%s.png?width=%d', $image, self::TARGET_WIDTH), $this->nonOwnerToken);

    self::assertSame(self::SOURCE_SIZE, $this->decodedWidth($served), 'Non-owner unsigned transform was not dropped.');
  }

  #[Test]
  public function nonOwnerTamperedTransformIsDropped(): void {
    $this->setAccessSettings(['requireAuthForMediaShares' => false]);
    $this->bootActors();
    $image = $this->uploadSizedPng($this->token);
    $this->publishImageShareWithWidth($image, self::TARGET_WIDTH);

    $foreign = $this->signature()->sign($image, ['width' => self::SOURCE_SIZE]);
    $served = $this->fetchContentWithToken(
      \sprintf('/api/image/%s.png?width=%d&s=%s', $image, self::TARGET_WIDTH, $foreign),
      $this->nonOwnerToken,
    );

    self::assertSame(self::SOURCE_SIZE, $this->decodedWidth($served), 'Non-owner tampered transform was not dropped.');
  }

  #[Test]
  public function guestUnsignedTransformIsDropped(): void {
    $this->setAccessSettings(['allowUnauthenticatedAccess' => true, 'requireAuthForMediaShares' => false]);
    $this->bootUser();
    $image = $this->uploadSizedPng($this->token);
    $this->publishImageShareWithWidth($image, self::TARGET_WIDTH);

    $served = $this->fetchContentWithToken(\sprintf('/api/image/%s.png?width=%d', $image, self::TARGET_WIDTH), null);

    self::assertSame(self::SOURCE_SIZE, $this->decodedWidth($served), 'Guest unsigned transform was not dropped.');
  }

  #[Test]
  public function nonOwnerSignedShareTransformResizes(): void {
    $this->setAccessSettings(['requireAuthForMediaShares' => false]);
    $this->bootActors();
    $image = $this->uploadSizedPng($this->token);
    $this->publishImageShareWithWidth($image, self::TARGET_WIDTH);

    $valid = $this->signature()->sign($image, ['width' => self::TARGET_WIDTH]);
    $served = $this->fetchContentWithToken(
      \sprintf('/api/image/%s.png?width=%d&s=%s', $image, self::TARGET_WIDTH, $valid),
      $this->nonOwnerToken,
    );

    self::assertSame(self::TARGET_WIDTH, $this->decodedWidth($served), 'Non-owner signed share transform did not resize.');
  }
}
