<?php

declare(strict_types=1);

namespace Slink\Share\Application\Command\RevokeShare;

use Slink\Share\Domain\Repository\ShareStoreRepositoryInterface;
use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\ValueObject\ID;

final readonly class RevokeShareHandler implements CommandHandlerInterface {
  public function __construct(
    private ShareStoreRepositoryInterface $shareStore,
  ) {}

  public function __invoke(RevokeShareCommand $command): void {
    $share = $this->shareStore->get(ID::fromString($command->getShareId()));

    if (!$share->aggregateRootVersion()) {
      return;
    }

    $share->revoke();
    $this->shareStore->store($share);
  }
}
