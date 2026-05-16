<?php

declare(strict_types=1);

namespace Slink\Share\Application\Command\UnlockShare;

use Slink\Share\Domain\Enum\ShareAccess;
use Slink\Share\Domain\Exception\InvalidSharePasswordException;
use Slink\Share\Domain\Repository\ShareStoreRepositoryInterface;
use Slink\Share\Infrastructure\Security\ShareUnlockCookieSigner;
use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final readonly class UnlockShareHandler implements CommandHandlerInterface {
  public function __construct(
    private ShareStoreRepositoryInterface $shareStore,
    private ShareUnlockCookieSigner $signer,
    private AuthorizationCheckerInterface $access,
  ) {}

  public function __invoke(UnlockShareCommand $command, string $shareId): ?Cookie {
    $id = ID::fromString($shareId);
    $share = $this->shareStore->get($id);

    if (!$share->aggregateRootVersion()) {
      throw new NotFoundException();
    }

    if (!$this->access->isGranted(ShareAccess::Unlock, $share)) {
      throw new NotFoundException();
    }

    $password = $share->getPassword();

    if ($password === null) {
      return null;
    }

    if (!$password->match($command->getPassword())) {
      throw new InvalidSharePasswordException();
    }

    return $this->signer->issueCookie($id, $password);
  }
}
