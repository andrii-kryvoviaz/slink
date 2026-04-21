<?php

declare(strict_types=1);

namespace Slink\Share\Application\Command\SetSharePassword;

use Slink\Share\Domain\Enum\ShareAccess;
use Slink\Share\Domain\Exception\ShareAccessDeniedException;
use Slink\Share\Domain\Repository\ShareStoreRepositoryInterface;
use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final readonly class SetSharePasswordHandler implements CommandHandlerInterface {
  public function __construct(
    private ShareStoreRepositoryInterface $shareStore,
    private AuthorizationCheckerInterface $access,
  ) {}

  public function __invoke(SetSharePasswordCommand $command, string $shareId): void {
    $share = $this->shareStore->get(ID::fromString($shareId));

    if (!$share->aggregateRootVersion()) {
      throw new NotFoundException();
    }

    if (!$this->access->isGranted(ShareAccess::Edit, $share)) {
      throw new ShareAccessDeniedException();
    }

    $share->setPassword($command->getPassword());
    $this->shareStore->store($share);
  }
}
