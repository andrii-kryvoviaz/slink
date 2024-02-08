<?php

declare(strict_types=1);

namespace Tests\Unit\UI\Http\Rest\Response;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Slik\Shared\Application\Http\Item;
use Slik\Shared\Infrastructure\Exception\NotFoundException;
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
    
    $payload = Item::fromContent($content);
    $response = ContentResponse::file($payload, $contentType, $status);
    
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
        'payload' => null,
        'contentType' => 'application/json',
        'expectedException' => NotFoundException::class
      ],
      'type-error' => [
        'payload' => 123,
        'contentType' => 'application/json',
        'expectedException' => \TypeError::class
      ],
      'empty string' => [
        'payload' => '',
        'contentType' => 'application/json',
      ],
      'image/png' => [
        'payload' => 'binary data',
        'contentType' => 'image/png',
      ],
    ];
  }
}