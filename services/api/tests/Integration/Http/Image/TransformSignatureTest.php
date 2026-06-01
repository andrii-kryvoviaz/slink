<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Image;

use PHPUnit\Framework\Attributes\Test;
use Slink\Shared\Domain\Service\UrlSignatureInterface;
use Tests\Integration\Http\HttpTestCase;

final class TransformSignatureTest extends HttpTestCase {
  private string $token = '';

  private function bootUser(): void {
    $this->createUser('transform-owner@local.test', 'transformowner', self::PASSWORD);
    $this->token = $this->login('transformowner', self::PASSWORD);
  }

  private function signature(): UrlSignatureInterface {
    /** @var UrlSignatureInterface $signature */
    $signature = static::getContainer()->get(UrlSignatureInterface::class);

    return $signature;
  }

  private function fetchContent(string $path): string {
    $this->client->request('GET', $path, [], [], ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->token]);

    $response = $this->client->getResponse();
    self::assertSame(200, $response->getStatusCode(), 'Image content request failed: ' . (string) $response->getContent());

    $content = $response->getContent();

    return $content === false ? '' : $content;
  }

  #[Test]
  public function signedTransformIsApplied(): void {
    $this->bootUser();
    $image = $this->uploadImage($this->token, false);

    $original = $this->fetchContent(\sprintf('/api/image/%s.png', $image));

    $valid = $this->signature()->sign($image, ['width' => 64]);
    $transformed = $this->fetchContent(\sprintf('/api/image/%s.png?width=64&s=%s', $image, $valid));

    self::assertNotSame($original, $transformed);
  }

  #[Test]
  public function transformWithoutSignatureFallsBackToOriginal(): void {
    $this->bootUser();
    $image = $this->uploadImage($this->token, false);

    $original = $this->fetchContent(\sprintf('/api/image/%s.png', $image));
    $unsigned = $this->fetchContent(\sprintf('/api/image/%s.png?width=64', $image));

    self::assertSame($original, $unsigned);
  }

  #[Test]
  public function transformWithTamperedSignatureFallsBackToOriginal(): void {
    $this->bootUser();
    $image = $this->uploadImage($this->token, false);

    $original = $this->fetchContent(\sprintf('/api/image/%s.png', $image));

    $valid = $this->signature()->sign($image, ['width' => 64]);
    $tampered = $this->fetchContent(\sprintf('/api/image/%s.png?width=128&s=%s', $image, $valid));

    self::assertSame($original, $tampered);
  }
}
