<?php

declare(strict_types=1);

namespace Tests\Unit\UI\Http\Rest\Controller\Comment;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Comment\Application\Query\GetCommentsByImage\GetCommentsByImageQuery;
use Slink\Shared\Application\Http\Collection;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Application\Query\QueryBusInterface;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mercure\Authorization;
use Symfony\Component\Mercure\HubRegistry;
use Symfony\Component\Mercure\Jwt\DefaultClaimsTokenFactory;
use Symfony\Component\Mercure\Jwt\LcobucciFactory;
use Symfony\Component\Mercure\Jwt\StaticTokenProvider;
use Symfony\Component\Mercure\MockHub;
use UI\Http\Rest\Controller\Comment\GetCommentsController;
use UI\Http\Rest\Response\ApiResponse;

final class GetCommentsControllerTest extends TestCase {
  private const array CLAIMS = [
    'iss' => 'slink',
    'aud' => 'https://mercure.slink.example',
    'sub' => 'slink',
    'client_id' => 'slink',
  ];

  #[Test]
  public function itReturnsCommentsSuccessfully(): void {
    $queryBus = $this->createMock(QueryBusInterface::class);
    $imageId = 'image-123';
    $collection = new Collection(1, 50, 0, []);

    $queryBus->expects($this->once())
      ->method('ask')
      ->with($this->callback(function ($query) {
        return $query instanceof GetCommentsByImageQuery;
      }))
      ->willReturn($collection);

    $controller = new GetCommentsController();
    $controller->setQueryBus($queryBus);

    $response = $controller(new Request(), $this->createAuthorization(), $imageId);

    $this->assertInstanceOf(ApiResponse::class, $response);
    $this->assertEquals(200, $response->getStatusCode());
  }

  #[Test]
  public function itPassesImageIdToQuery(): void {
    $queryBus = $this->createMock(QueryBusInterface::class);
    $imageId = 'specific-image-id';
    $collection = new Collection(0, 50, 0, []);

    $queryBus->expects($this->once())
      ->method('ask')
      ->with($this->callback(function ($query) use ($imageId) {
        return $query instanceof GetCommentsByImageQuery && $query->getImageId() === $imageId;
      }))
      ->willReturn($collection);

    $controller = new GetCommentsController();
    $controller->setQueryBus($queryBus);

    $controller(new Request(), $this->createAuthorization(), $imageId);
  }

  #[Test]
  public function itHandlesPaginationParameters(): void {
    $queryBus = $this->createMock(QueryBusInterface::class);
    $imageId = 'image-123';
    $page = 2;
    $limit = 25;
    $collection = new Collection(100, $limit, ($page - 1) * $limit, []);

    $queryBus->expects($this->once())
      ->method('ask')
      ->with($this->callback(function ($query) use ($page, $limit) {
        return $query instanceof GetCommentsByImageQuery && $query->getPage() === $page && $query->getLimit() === $limit;
      }))
      ->willReturn($collection);

    $controller = new GetCommentsController();
    $controller->setQueryBus($queryBus);

    $controller(new Request(), $this->createAuthorization(), $imageId, $page, $limit);
  }

  #[Test]
  public function itClampsPageToMinimumOne(): void {
    $queryBus = $this->createMock(QueryBusInterface::class);

    $queryBus->expects($this->once())
      ->method('ask')
      ->with($this->callback(function ($query) {
        return $query instanceof GetCommentsByImageQuery && $query->getPage() === 1;
      }))
      ->willReturn(new Collection(0, 50, 0, []));

    $controller = new GetCommentsController();
    $controller->setQueryBus($queryBus);

    $controller(new Request(), $this->createAuthorization(), 'image-123', 0);
  }

  #[Test]
  public function itClampsLimitToMaximumOneHundred(): void {
    $queryBus = $this->createMock(QueryBusInterface::class);

    $queryBus->expects($this->once())
      ->method('ask')
      ->with($this->callback(function ($query) {
        return $query instanceof GetCommentsByImageQuery && $query->getLimit() === 100;
      }))
      ->willReturn(new Collection(0, 100, 0, []));

    $controller = new GetCommentsController();
    $controller->setQueryBus($queryBus);

    $controller(new Request(), $this->createAuthorization(), 'image-123', 1, 500);
  }

  #[Test]
  public function itClampsLimitToMinimumOne(): void {
    $queryBus = $this->createMock(QueryBusInterface::class);

    $queryBus->expects($this->once())
      ->method('ask')
      ->with($this->callback(function ($query) {
        return $query instanceof GetCommentsByImageQuery && $query->getLimit() === 1;
      }))
      ->willReturn(new Collection(0, 1, 0, []));

    $controller = new GetCommentsController();
    $controller->setQueryBus($queryBus);

    $controller(new Request(), $this->createAuthorization(), 'image-123', 1, -5);
  }

  #[Test]
  public function itUsesDefaultPaginationValues(): void {
    $queryBus = $this->createMock(QueryBusInterface::class);
    $imageId = 'image-123';
    $collection = new Collection(0, 20, 0, []);

    $queryBus->expects($this->once())
      ->method('ask')
      ->with($this->callback(function ($query) {
        return $query instanceof GetCommentsByImageQuery && $query->getPage() === 1 && $query->getLimit() === 20;
      }))
      ->willReturn($collection);

    $controller = new GetCommentsController();
    $controller->setQueryBus($queryBus);

    $controller(new Request(), $this->createAuthorization(), $imageId);
  }

  #[Test]
  public function itReturnsCollectionResponse(): void {
    $queryBus = $this->createStub(QueryBusInterface::class);
    $imageId = 'image-123';
    $collection = new Collection(5, 50, 0, [
      Item::fromPayload('comment', ['id' => 'comment-1', 'content' => 'First comment']),
      Item::fromPayload('comment', ['id' => 'comment-2', 'content' => 'Second comment']),
    ]);

    $queryBus->method('ask')->willReturn($collection);

    $controller = new GetCommentsController();
    $controller->setQueryBus($queryBus);

    $response = $controller(new Request(), $this->createAuthorization(), $imageId);

    $this->assertInstanceOf(ApiResponse::class, $response);
    $this->assertEquals(200, $response->getStatusCode());
  }

  #[Test]
  #[Group('sc-878')]
  public function itSetsMercureAccessTokenCookieOnSuccess(): void {
    $queryBus = $this->createStub(QueryBusInterface::class);
    $imageId = 'image-123';
    $queryBus->method('ask')->willReturn(new Collection(0, 50, 0, []));

    $controller = new GetCommentsController();
    $controller->setQueryBus($queryBus);

    $request = new Request();
    $controller($request, $this->createAuthorization(), $imageId);

    /** @var array<string, Cookie> $cookies */
    $cookies = $request->attributes->get('_mercure_authorization_cookies', []);
    $cookie = array_values($cookies)[0] ?? null;

    $this->assertInstanceOf(Cookie::class, $cookie);
    $this->assertSame('mercure_access_token', $cookie->getName());
    $this->assertSame('/sse', $cookie->getPath());

    $payload = base64_decode(strtr(explode('.', (string) $cookie->getValue())[1], '-_', '+/'));
    $this->assertStringContainsString('comments/image/image-123', $payload);
  }

  #[Test]
  #[Group('sc-878')]
  public function itMintsAccessTokenWithRegisteredClaimsAndExactCommentSubscribeGrant(): void {
    $queryBus = $this->createStub(QueryBusInterface::class);
    $queryBus->method('ask')->willReturn(new Collection(0, 50, 0, []));

    $controller = new GetCommentsController();
    $controller->setQueryBus($queryBus);

    $request = new Request();
    $before = time();
    $controller($request, $this->createAuthorization(), 'image-123');

    /** @var array<string, Cookie> $cookies */
    $cookies = $request->attributes->get('_mercure_authorization_cookies', []);
    $cookie = array_values($cookies)[0] ?? null;
    $this->assertInstanceOf(Cookie::class, $cookie);

    /** @var array<string, mixed> $payload */
    $payload = json_decode(
      (string) base64_decode(strtr(explode('.', (string) $cookie->getValue())[1], '-_', '+/')),
      true,
      512,
      JSON_THROW_ON_ERROR,
    );

    $this->assertSame('slink', $payload['iss'] ?? null);
    $this->assertSame(['https://mercure.slink.example'], (array) ($payload['aud'] ?? []));
    $this->assertSame('slink', $payload['sub'] ?? null);
    $this->assertSame('slink', $payload['client_id'] ?? null);
    $this->assertIsNumeric($payload['exp'] ?? null);
    $this->assertGreaterThanOrEqual($before + 1800, (int) $payload['exp']);
    $this->assertLessThanOrEqual(time() + 1800, (int) $payload['exp']);
    $this->assertSame([[
      'type' => 'https://mercure.rocks/authorization-detail',
      'actions' => ['subscribe'],
      'topics' => [['match' => 'comments/image/image-123']],
    ]], $payload['authorization_details'] ?? null);
  }

  #[Test]
  public function itDoesNotSetMercureAuthorizationCookieWhenQueryFails(): void {
    $queryBus = $this->createStub(QueryBusInterface::class);
    $queryBus->method('ask')->willThrowException(new NotFoundException());

    $controller = new GetCommentsController();
    $controller->setQueryBus($queryBus);

    $request = new Request();

    try {
      $controller($request, $this->createAuthorization(), 'image-123');
      $this->fail('Expected NotFoundException to be thrown');
    } catch (NotFoundException) {
      $this->assertSame([], $request->attributes->get('_mercure_authorization_cookies', []));
    }
  }

  private function createAuthorization(): Authorization {
    $hub = new MockHub(
      'http://localhost:3333/.well-known/mercure',
      new StaticTokenProvider('test.jwt.token'),
      fn(): string => 'id',
      new DefaultClaimsTokenFactory(
        new LcobucciFactory('a-string-that-is-at-least-256-bits-long-for-hs256'),
        self::CLAIMS,
      ),
      '/sse',
      'mercure_access_token',
    );

    return new Authorization(new HubRegistry($hub), 1800);
  }
}
