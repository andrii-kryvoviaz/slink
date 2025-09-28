<?php

declare(strict_types=1);

namespace Tests\Unit\UI\Http\Rest\Controller\Tag;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Application\Query\QueryBusInterface;
use Slink\Tag\Application\Query\GetTagById\GetTagByIdQuery;
use Slink\Tag\Domain\Tag;
use Slink\Tag\Domain\ValueObject\TagName;
use Slink\Tag\Infrastructure\ReadModel\View\TagView;
use Slink\User\Domain\Contracts\UserInterface;
use UI\Http\Rest\Controller\Tag\GetTagByIdController;
use UI\Http\Rest\Response\ApiResponse;

final class GetTagByIdControllerTest extends TestCase {

  #[Test]
  public function itReturnsTagById(): void {
    $queryBus = $this->createMock(QueryBusInterface::class);
    $user = $this->createMock(UserInterface::class);
    $item = Item::fromPayload('tag', ['id' => 'tag-123', 'name' => 'test-tag']);
    
    $user->expects($this->once())
      ->method('getIdentifier')
      ->willReturn('user-123');

    $queryBus->expects($this->once())
      ->method('ask')
      ->with($this->isInstanceOf(\Symfony\Component\Messenger\Envelope::class))
      ->willReturn($item);

    $controller = new GetTagByIdController();
    $controller->setQueryBus($queryBus);

    $response = $controller('tag-123', $user);

    $this->assertInstanceOf(ApiResponse::class, $response);
    $this->assertEquals(200, $response->getStatusCode());
  }

  #[Test]
  public function itCreatesQueryWithCorrectId(): void {
    $queryBus = $this->createMock(QueryBusInterface::class);
    $user = $this->createMock(UserInterface::class);
    $item = Item::fromPayload('tag', ['id' => 'tag-456', 'name' => 'test-tag']);
    
    $user->expects($this->once())
      ->method('getIdentifier')
      ->willReturn('user-456');

    $queryBus->expects($this->once())
      ->method('ask')
      ->with($this->callback(function ($envelope) {
        return $envelope instanceof \Symfony\Component\Messenger\Envelope 
          && $envelope->getMessage() instanceof GetTagByIdQuery;
      }))
      ->willReturn($item);

    $controller = new GetTagByIdController();
    $controller->setQueryBus($queryBus);

    $response = $controller('tag-456', $user);

    $this->assertEquals(200, $response->getStatusCode());
  }

  #[Test]
  public function itReturnsOneResponse(): void {
    $queryBus = $this->createMock(QueryBusInterface::class);
    $user = $this->createMock(UserInterface::class);
    $item = Item::fromPayload('tag', ['id' => 'tag-789', 'name' => 'test-tag']);
    
    $user->method('getIdentifier')->willReturn('user-789');
    $queryBus->method('ask')->willReturn($item);

    $controller = new GetTagByIdController();
    $controller->setQueryBus($queryBus);

    $response = $controller('tag-789', $user);

    $this->assertInstanceOf(ApiResponse::class, $response);
    $this->assertEquals(200, $response->getStatusCode());
  }
}