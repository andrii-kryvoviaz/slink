<?php

declare(strict_types=1);

namespace Unit\Slink\User\Infrastructure\Specification;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\User\Domain\Exception\DuplicateOAuthProviderException;
use Slink\User\Domain\ValueObject\OAuth\ProviderSlug;
use Slink\User\Domain\Repository\OAuthProviderRepositoryInterface;
use Slink\User\Infrastructure\ReadModel\View\OAuthProviderView;
use Slink\User\Infrastructure\Specification\UniqueOAuthProviderSpecification;

final class UniqueOAuthProviderSpecificationTest extends TestCase {

  #[Test]
  public function itPassesWhenNoExistingProvider(): void {
    $repository = $this->createMock(OAuthProviderRepositoryInterface::class);
    $repository->expects($this->once())
      ->method('findByProvider')
      ->with(ProviderSlug::fromString('google'))
      ->willReturn(null);

    $specification = new UniqueOAuthProviderSpecification($repository);

    $specification->ensureUnique(ProviderSlug::fromString('google'));

    $this->addToAssertionCount(1);
  }

  #[Test]
  public function itThrowsWhenProviderExists(): void {
    $existingView = $this->createStub(OAuthProviderView::class);

    $repository = $this->createMock(OAuthProviderRepositoryInterface::class);
    $repository->expects($this->once())
      ->method('findByProvider')
      ->with(ProviderSlug::fromString('google'))
      ->willReturn($existingView);

    $specification = new UniqueOAuthProviderSpecification($repository);

    $this->expectException(DuplicateOAuthProviderException::class);

    $specification->ensureUnique(ProviderSlug::fromString('google'));
  }
}
