<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\RefreshToken;

use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\Exception\Date\DateTimeException;
use Slink\User\Domain\Exception\InvalidRefreshToken;
use Slink\User\Domain\Repository\UserStoreRepositoryInterface;
use Slink\User\Domain\ValueObject\Auth\HashedRefreshToken;

final readonly class RefreshTokenHandler implements CommandHandlerInterface {
  
  public function __construct(
    private UserStoreRepositoryInterface $userStore,
  ) {}
  
  /**
   * @throws DateTimeException
   */
  public function __invoke(RefreshTokenCommand $command, string $updatedRefreshToken): void {
    $currentHashedRefreshToken = HashedRefreshToken::encode($command->getRefreshToken());
    $updatedHashedRefreshToken = HashedRefreshToken::encode($updatedRefreshToken);
    
    $user = $this->userStore->getByRefreshToken($currentHashedRefreshToken);
    
    if($user === null) {
      throw new InvalidRefreshToken();
    }
    
    $user->refreshToken->rotate($currentHashedRefreshToken, $updatedHashedRefreshToken);
    
    $this->userStore->store($user);
  }
}