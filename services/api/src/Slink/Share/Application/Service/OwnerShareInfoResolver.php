<?php

declare(strict_types=1);

namespace Slink\Share\Application\Service;

use Slink\Share\Domain\Enum\ShareableType;
use Slink\Share\Domain\Repository\ShareRepositoryInterface;
use Slink\Share\Domain\Service\OwnerShareInfoResolverInterface;
use Slink\Share\Domain\Service\ShareServiceInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

#[AsAlias(OwnerShareInfoResolverInterface::class)]
final readonly class OwnerShareInfoResolver implements OwnerShareInfoResolverInterface {
  public function __construct(
    private ShareRepositoryInterface $shareRepository,
    private ShareServiceInterface $shareService,
  ) {}

  public function resolve(string $shareableId, ShareableType $type, ID $ownerId, ?ID $viewerId): array {
    if ($viewerId === null) {
      return [];
    }

    if (!$ownerId->equals($viewerId)) {
      return [];
    }

    $share = $this->shareRepository->findByShareable($shareableId, $type);

    if ($share === null) {
      return [];
    }

    return [
      'shareInfo' => [
        'shareId' => $share->getId(),
        'shareUrl' => $this->shareService->resolveUrl($share),
        'type' => $type->value,
      ],
    ];
  }
}
