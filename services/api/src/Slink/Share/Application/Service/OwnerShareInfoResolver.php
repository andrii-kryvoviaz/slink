<?php

declare(strict_types=1);

namespace Slink\Share\Application\Service;

use Slink\Share\Domain\Enum\ShareableType;
use Slink\Share\Domain\Repository\ShareRepositoryInterface;
use Slink\Share\Domain\Service\OwnerShareInfoResolverInterface;
use Slink\Share\Domain\Service\ShareServiceInterface;
use Slink\Share\Domain\ValueObject\ShareResponse;
use Slink\Shared\Domain\ValueObject\ID;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

#[AsAlias(OwnerShareInfoResolverInterface::class)]
final readonly class OwnerShareInfoResolver implements OwnerShareInfoResolverInterface {
  public function __construct(
    private ShareRepositoryInterface $shareRepository,
    private ShareServiceInterface $shareService,
  ) {}

  public function resolve(
    string $shareableId,
    ShareableType $type,
    ?ID $ownerId,
    ?ID $viewerId,
  ): ?ShareResponse {
    if (!$ownerId?->equals($viewerId)) {
      return null;
    }

    $share = $this->shareRepository->findByShareable($shareableId, $type);

    if ($share === null) {
      return null;
    }

    return ShareResponse::fromShare($share, $this->shareService->resolveUrl($share));
  }
}
