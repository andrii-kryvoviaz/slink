<?php

declare(strict_types=1);

namespace Tests\Unit\UI\Http\Rest\Response;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Application\Http\Collection;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Symfony\Component\HttpFoundation\Response;
use UI\Http\Rest\Response\ApiResponse;

final class ApiResponseTest extends TestCase {
  
  /**
   * @param mixed $payload
   * @param class-string<\Throwable>|null $expectedException
   * @param int $status
   * @return void
   */
  #[DataProvider('providePayloads')]
  public function testFromPayload(mixed $payload, ?string $expectedException = null, int $status = Response::HTTP_OK,): void {
    if ($expectedException) {
      $this->expectException($expectedException);
    }
    
    $response = ApiResponse::fromPayload($payload, $status);
    
    $this->assertResponseContentEquals($payload, $response);
    $this->assertEquals($status, $response->getStatusCode());
  }
  
  /**
   * @return void
   */
  public function testEmpty(): void {
    $status = Response::HTTP_NO_CONTENT;
    
    $response = ApiResponse::empty($status);
    
    $this->assertResponseContentEquals([], $response);
    $this->assertEquals($status, $response->getStatusCode());
  }
  
  /**
   * @return void
   */
  public function testCreated(): void {
    $id = '123';
    $location = '/path/to/resource';
    
    $response = ApiResponse::created($id, $location);
    
    $this->assertResponseContentEquals(['id' => $id], $response);
    $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
    $this->assertEquals($location, $response->headers->get('location'));
  }
  
  /**
   * @param mixed $payload
   * @param class-string<\Throwable>|null $expectedException
   * @param int $status
   * @return void
   */
  #[DataProvider('providePayloads')]
  public function testSingleItemResponse(mixed $payload, ?string $expectedException = null, int $status = Response::HTTP_OK): void {
    if ($expectedException) {
      $this->expectException($expectedException);
    }
    
    $item = Item::fromPayload('type', $payload);
    
    $response = ApiResponse::one($item, $status);
    
    $expectedResponseData = $this->createExpectedResponseData($item);
    
    $this->assertResponseContentEquals($expectedResponseData, $response);
    $this->assertEquals($status, $response->getStatusCode());
  }
  
  /**
   * @param mixed $payload
   * @param class-string<\Throwable>|null $expectedException
   * @param int $status
   * @return void
   * @throws NotFoundException
   */
  #[DataProvider('providePayloads')]
  public function testCollectionResponse(mixed $payload, ?string $expectedException = null, int $status = Response::HTTP_OK): void {
    if ($expectedException) {
      $this->expectException($expectedException);
    }
    
    $item = Item::fromPayload('type', $payload);
    
    $collection = new Collection(page: 1, limit: 1, total: 1, data: [$item]);
    $response = ApiResponse::collection($collection, $status);
    
    $expectedResponseData = $this->createExpectedResponseData($collection);
    
    $this->assertResponseContentEquals($expectedResponseData, $response);
    $this->assertEquals($status, $response->getStatusCode());
  }
  
  /**
   * @param array<string, mixed> $expectedContent
   * @param ApiResponse $response
   * @return void
   */
  private function assertResponseContentEquals(array $expectedContent, ApiResponse $response): void {
    $actualContent = \json_decode((string)$response->getContent(), true);
    $this->assertEquals($expectedContent, $actualContent);
  }
  
  /**
   * @param mixed $data
   * @return array<string, mixed>
   */
  private function createExpectedResponseData(mixed $data): array {
    return $data instanceof Collection
      ? [
        'meta' => [
          'size' => 1,
          'page' => 1,
          'total' => 1,
        ],
        'data' => [iterator_to_array($data->data)[0]->resource],
      ]
      : ['data' => $data->resource];
  }
  
  /**
   * @return array<string, mixed>
   */
  public static function providePayloads(): array {
    return [
      'empty payload' => [
        'payload' => [],
      ],
      'payload attributes' => [
        'payload' => [
          'attributes' => [
            'attribute' => 'value',
          ],
        ],
      ],
      'non-array payload' => [
        'payload' => 'string',
        'expectedException' => \TypeError::class,
      ],
      'invalid status' => [
        'payload' => [],
        'expectedException' => \InvalidArgumentException::class,
        'status' => 999,
      ],
    ];
  }
}