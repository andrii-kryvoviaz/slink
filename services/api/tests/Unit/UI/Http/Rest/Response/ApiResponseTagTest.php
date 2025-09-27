<?php

declare(strict_types=1);

namespace Tests\Unit\UI\Http\Rest\Response;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Application\Http\Collection;
use Slink\Shared\Application\Http\Item;
use Slink\Tag\Infrastructure\ReadModel\View\TagView;
use UI\Http\Rest\Response\ApiResponse;

final class ApiResponseTagTest extends TestCase {

  #[Test]
  public function itCreatesSuccessfulResponseForTagCreation(): void {
    $tagId = 'tag-123';
    $location = "tags/{$tagId}";
    
    $response = ApiResponse::created($tagId, $location);
    
    $this->assertEquals(201, $response->getStatusCode());
    $this->assertEquals($location, $response->headers->get('Location'));
  }

  #[Test]
  public function itCreatesEmptyResponseForTagDeletion(): void {
    $response = ApiResponse::empty();
    
    $this->assertEquals(204, $response->getStatusCode());
  }

  #[Test]
  public function itCreatesOneResponseForSingleTag(): void {
    $tagView = $this->createMock(TagView::class);
    $item = Item::fromEntity($tagView);
    $response = ApiResponse::one($item);
    
    $this->assertEquals(200, $response->getStatusCode());
  }

  #[Test]
  public function itCreatesCollectionResponseForTagList(): void {
    $tagView1 = $this->createMock(TagView::class);
    $tagView2 = $this->createMock(TagView::class);
    $items = [Item::fromEntity($tagView1), Item::fromEntity($tagView2)];
    $collection = new Collection(1, 10, 2, $items);
    
    $response = ApiResponse::collection($collection);
    
    $this->assertEquals(200, $response->getStatusCode());
  }

  #[Test]
  public function itCreatesListResponseForArrayOfItems(): void {
    $tagView = $this->createMock(TagView::class);
    $items = [Item::fromEntity($tagView)];
    
    $response = ApiResponse::list($items);
    
    $this->assertEquals(200, $response->getStatusCode());
  }

  #[Test]
  public function itCreatesFromPayloadForArbitraryData(): void {
    $payload = [
      'tags' => [
        ['id' => 'tag-1', 'name' => 'tag1'],
        ['id' => 'tag-2', 'name' => 'tag2']
      ],
      'total' => 2
    ];
    
    $response = ApiResponse::fromPayload($payload);
    
    $this->assertEquals(200, $response->getStatusCode());
  }

  #[Test]
  public function itHandlesEmptyTagCollection(): void {
    $emptyCollection = new Collection(1, 10, 0, []);
    $response = ApiResponse::collection($emptyCollection);
    
    $this->assertEquals(200, $response->getStatusCode());
  }

  #[Test]
  public function itHandlesEmptyItemList(): void {
    $emptyItems = [];
    $response = ApiResponse::list($emptyItems);
    
    $this->assertEquals(200, $response->getStatusCode());
  }

  #[Test]
  public function itSetsCorrectContentTypeHeader(): void {
    $tagView = $this->createMock(TagView::class);
    $item = Item::fromEntity($tagView);
    $response = ApiResponse::one($item);
    
    $this->assertEquals('application/json', $response->headers->get('Content-Type'));
  }

  #[Test]
  public function itCreatesResponsesWithDifferentStatusCodes(): void {
    $tagId = 'new-tag';
    $tagView = $this->createMock(TagView::class);
    $item = Item::fromEntity($tagView);
    $collection = new Collection(1, 10, 1, [$item]);
    
    $createdResponse = ApiResponse::created($tagId, "tags/{$tagId}");
    $okResponse = ApiResponse::one($item);
    $collectionResponse = ApiResponse::collection($collection);
    $emptyResponse = ApiResponse::empty();
    
    $this->assertEquals(201, $createdResponse->getStatusCode());
    $this->assertEquals(200, $okResponse->getStatusCode());
    $this->assertEquals(200, $collectionResponse->getStatusCode());
    $this->assertEquals(204, $emptyResponse->getStatusCode());
  }

  #[Test]
  public function itCreatesValidJsonResponseStructure(): void {
    $tagView = $this->createMock(TagView::class);
    $item = Item::fromEntity($tagView);
    $response = ApiResponse::one($item);
    
    $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
    $this->assertJson($response->getContent() ?: '');
  }

  #[Test]
  public function itCreatesCollectionWithMetadata(): void {
    $tagView = $this->createMock(TagView::class);
    $item = Item::fromEntity($tagView);
    $collection = new Collection(2, 5, 15, [$item]);
    
    $response = ApiResponse::collection($collection);
    $content = json_decode($response->getContent() ?: '', true);
    
    $this->assertArrayHasKey('meta', $content);
    $this->assertEquals(2, $content['meta']['page']);
    $this->assertEquals(5, $content['meta']['size']);
    $this->assertEquals(15, $content['meta']['total']);
    $this->assertArrayHasKey('data', $content);
  }
}