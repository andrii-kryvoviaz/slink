<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\UpdateProfile;

use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\Repository\UserStoreRepositoryInterface;
use Slink\User\Domain\Specification\UniqueDisplayNameSpecificationInterface;
use Slink\User\Domain\ValueObject\DisplayName;

final readonly class UpdateProfileHandler implements CommandHandlerInterface {
  public function __construct(
    private UserStoreRepositoryInterface $store,
    private UniqueDisplayNameSpecificationInterface $uniqueDisplayNameSpecification
  ) {
  
  }
  
  public function __invoke(UpdateProfileCommand $command, string $userId): void {
    $user = $this->store->get(ID::fromString($userId));
    
    if ($command->getDisplayName()) {
      $displayName = DisplayName::fromString($command->getDisplayName());
      $user->changeDisplayName($displayName, $this->uniqueDisplayNameSpecification);
    }
    
    $this->store->store($user);
  }
}