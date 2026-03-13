<?php

declare(strict_types=1);

namespace Unit\Slink\User\Application\Query\GetOAuthProviders;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Slink\User\Application\Query\GetOAuthProviders\GetOAuthProvidersHandler;
use Slink\User\Application\Query\GetOAuthProviders\GetOAuthProvidersQuery;
use Slink\User\Domain\ValueObject\OAuth\ProviderSlug;
use Slink\User\Domain\Filter\OAuthProviderFilter;
use Slink\User\Domain\Repository\OAuthProviderRepositoryInterface;
use Slink\User\Infrastructure\ReadModel\View\OAuthProviderView;

final class GetOAuthProvidersHandlerTest extends TestCase {
  private OAuthProviderRepositoryInterface&MockObject $providerRepository;
  private GetOAuthProvidersHandler $handler;

  protected function setUp(): void {
    $this->providerRepository = $this->createMock(OAuthProviderRepositoryInterface::class);
    $this->handler = new GetOAuthProvidersHandler($this->providerRepository);
  }

  #[Test]
  public function itReturnsMappedItemArray(): void {
    $query = new GetOAuthProvidersQuery();

    $provider1 = $this->createStub(OAuthProviderView::class);
    $provider1->method('getSlug')->willReturn(ProviderSlug::fromString('google'));
    $provider2 = $this->createStub(OAuthProviderView::class);
    $provider2->method('getSlug')->willReturn(ProviderSlug::fromString('authentik'));

    $this->providerRepository
      ->expects($this->once())
      ->method('getProviders')
      ->with($this->isInstanceOf(OAuthProviderFilter::class))
      ->willReturn([$provider1, $provider2]);

    $result = $this->handler->__invoke($query);

    $this->assertCount(2, $result);
  }

  #[Test]
  public function itReturnsEmptyArrayForNoProviders(): void {
    $query = new GetOAuthProvidersQuery();

    $this->providerRepository
      ->expects($this->once())
      ->method('getProviders')
      ->with($this->isInstanceOf(OAuthProviderFilter::class))
      ->willReturn([]);

    $result = $this->handler->__invoke($query);

    $this->assertSame([], $result);
  }
}
