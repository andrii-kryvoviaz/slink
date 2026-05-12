<?php

declare(strict_types=1);

namespace Tests\Unit\UI\Http\Rest\Response;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Application\Http\CachePolicy;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Symfony\Component\HttpFoundation\Response;
use UI\Http\Rest\Response\ContentResponse;

final class ContentResponseTest extends TestCase {
  
  /**
   * @param mixed $content
   * @param string $contentType
   * @param class-string<\Throwable>|null $expectedException
   * @param int $status
   * @return void
   * @throws NotFoundException
   */
  #[DataProvider('providePayloads')]
  public function testFileMethod(mixed $content, string $contentType, ?string $expectedException = null, int $status = Response::HTTP_OK): void {
    if ($expectedException) {
      $this->expectException($expectedException);
    }
    
    $payload = Item::fromContent($content, $contentType);
    $response = ContentResponse::file($payload, $status);
    
    $this->assertEquals($status, $response->getStatusCode());
    $this->assertEquals($payload->resource, $response->getContent());
    $this->assertEquals($contentType, $response->headers->get('Content-Type'));
  }
  
  /**
   * @return array<string, mixed>
   */
  public static function providePayloads(): array {
    return [
      'null' => [
        'content' => null,
        'contentType' => 'application/json',
        'expectedException' => \TypeError::class
      ],
      'type-error' => [
        'content' => 123,
        'contentType' => 'application/json',
        'expectedException' => \TypeError::class
      ],
      'empty string' => [
        'content' => '',
        'contentType' => 'application/json',
      ],
      'image/png' => [
        'content' => 'binary data',
        'contentType' => 'image/png',
      ],
    ];
  }

  #[Test]
  public function publicImmutablePolicyEmitsPublicImmutableLongMaxAge(): void {
    $response = ContentResponse::file(Item::fromContent('bytes', 'image/png', CachePolicy::publicImmutable()));

    $header = $response->headers->get('Cache-Control') ?? '';

    $this->assertStringContainsString('public', $header);
    $this->assertStringContainsString('immutable', $header);
    $this->assertStringContainsString('max-age=31536000', $header);
    $this->assertStringNotContainsString('private', $header);
    $this->assertStringNotContainsString('no-cache', $header);
    $this->assertStringNotContainsString('must-revalidate', $header);
  }

  #[Test]
  public function privateImmutablePolicyEmitsPrivateImmutableLongMaxAge(): void {
    $response = ContentResponse::file(Item::fromContent('bytes', 'image/png', CachePolicy::privateImmutable()));

    $header = $response->headers->get('Cache-Control') ?? '';

    $this->assertStringContainsString('private', $header);
    $this->assertStringContainsString('immutable', $header);
    $this->assertStringContainsString('max-age=31536000', $header);
    $this->assertStringNotContainsString('public', $header);
    $this->assertStringNotContainsString('no-cache', $header);
    $this->assertStringNotContainsString('must-revalidate', $header);
  }

  #[Test]
  public function revocablePolicyEmitsPrivateNoCacheMustRevalidate(): void {
    $response = ContentResponse::file(Item::fromContent('bytes', 'image/png', CachePolicy::revocable()));

    $header = $response->headers->get('Cache-Control') ?? '';

    $this->assertStringContainsString('private', $header);
    $this->assertStringContainsString('no-cache', $header);
    $this->assertStringContainsString('must-revalidate', $header);
    $this->assertStringContainsString('max-age=0', $header);
    $this->assertStringNotContainsString('public', $header);
    $this->assertStringNotContainsString('immutable', $header);
  }

  #[Test]
  public function defaultPolicyIsRevocable(): void {
    $response = ContentResponse::file(Item::fromContent('bytes', 'image/png'));

    $header = $response->headers->get('Cache-Control') ?? '';

    $this->assertStringContainsString('private', $header);
    $this->assertStringContainsString('no-cache', $header);
    $this->assertStringContainsString('must-revalidate', $header);
  }
}