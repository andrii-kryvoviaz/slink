<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\CreateApiKey;

use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\Repository\UserStoreRepositoryInterface;

final readonly class CreateApiKeyHandler implements CommandHandlerInterface {
  public function __construct(
    private UserStoreRepositoryInterface $userRepository
  ) {}

  public function __invoke(CreateApiKeyCommand $command, string $userId): string {
    $user = $this->userRepository->get(ID::fromString($userId));
    
    $key = $user->createApiKey($command->getName(), $command->getExpiresAt());
    
    $this->userRepository->store($user);
    
    return $key;
  }
}
