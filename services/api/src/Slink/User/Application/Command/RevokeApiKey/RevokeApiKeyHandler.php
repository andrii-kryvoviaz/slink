<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\RevokeApiKey;

use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\Repository\UserStoreRepositoryInterface;

final readonly class RevokeApiKeyHandler implements CommandHandlerInterface {
  public function __construct(
    private UserStoreRepositoryInterface $userRepository
  ) {}

  public function __invoke(RevokeApiKeyCommand $command, string $userId): void {
    $user = $this->userRepository->get(ID::fromString($userId));
    
    $user->revokeApiKey($command->getKeyId());
    
    $this->userRepository->store($user);
  }
}
