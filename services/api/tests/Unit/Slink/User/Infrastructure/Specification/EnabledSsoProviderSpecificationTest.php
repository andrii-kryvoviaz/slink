<?php

declare(strict_types=1);

namespace Unit\Slink\User\Infrastructure\Specification;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\User\Domain\Filter\OAuthProviderFilter;
use Slink\User\Domain\Repository\OAuthProviderRepositoryInterface;
use Slink\User\Infrastructure\ReadModel\View\OAuthProviderView;
use Slink\User\Infrastructure\Specification\EnabledSsoProviderSpecification;

final class EnabledSsoProviderSpecificationTest extends TestCase {

  #[Test]
  public function itIsNotSatisfiedWhenNoProviderIsEnabled(): void {
    $repository = $this->createMock(OAuthProviderRepositoryInterface::class);
    $repository->expects($this->once())
      ->method('getProviders')
      ->with($this->callback(fn (OAuthProviderFilter $filter): bool => $filter->isEnabledOnly()))
      ->willReturn([]);

    $specification = new EnabledSsoProviderSpecification($repository);

    $this->assertFalse($specification->isSatisfied());
  }

  #[Test]
  public function itIsSatisfiedWhenAProviderIsEnabled(): void {
    $repository = $this->createMock(OAuthProviderRepositoryInterface::class);
    $repository->expects($this->once())
      ->method('getProviders')
      ->with($this->callback(fn (OAuthProviderFilter $filter): bool => $filter->isEnabledOnly()))
      ->willReturn([$this->createStub(OAuthProviderView::class)]);

    $specification = new EnabledSsoProviderSpecification($repository);

    $this->assertTrue($specification->isSatisfied());
  }
}
