<?php

declare(strict_types=1);

namespace Slink\Share\Application\Command\PublishShare;

use Slink\Share\Domain\Enum\ShareAccess;
use Slink\Share\Domain\Exception\ShareAccessDeniedException;
use Slink\Share\Domain\Repository\ShareStoreRepositoryInterface;
use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final readonly class PublishShareHandler implements CommandHandlerInterface {
  public function __construct(
    private ShareStoreRepositoryInterface $shareStore,
    private AuthorizationCheckerInterface $access,
  ) {}

  public function __invoke(PublishShareCommand $command): void {
    $share = $this->shareStore->get(ID::fromString($command->getShareId()));

    if (!$share->aggregateRootVersion()) {
      throw new NotFoundException();
    }

    if (!$this->access->isGranted(ShareAccess::Edit, $share)) {
      throw new ShareAccessDeniedException();
    }

    $share->publish();
    $this->shareStore->store($share);
  }
}
