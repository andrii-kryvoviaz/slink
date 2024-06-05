<?php

declare(strict_types=1);

namespace Slink\User\Application\Command\SignUp;

use Slink\Settings\Domain\Service\ConfigurationProvider;
use Slink\Shared\Application\Command\CommandHandlerInterface;
use Slink\Shared\Domain\Exception\Date\DateTimeException;
use Slink\User\Domain\Context\UserCreationContext;
use Slink\User\Domain\Enum\UserStatus;
use Slink\User\Domain\Repository\UserStoreRepositoryInterface;
use Slink\User\Domain\User;
use Slink\User\Domain\ValueObject\Auth\Credentials;
use Slink\User\Domain\ValueObject\DisplayName;

final readonly class SignUpHandler implements CommandHandlerInterface {

  public function __construct(
    private ConfigurationProvider $configurationProvider,
    private UserStoreRepositoryInterface $userRepository,
    private UserCreationContext $userCreationContext
  ) {
  }

  /**
   * @throws DateTimeException
   */
  public function __invoke(SignUpCommand $command): void {
    $credentials = Credentials::fromPlainCredentials($command->getEmail(), $command->getPassword());
    $displayName = DisplayName::fromString($command->getDisplayName());
    
    $status = $this->configurationProvider->get('user.approvalRequired') ? UserStatus::Inactive : UserStatus::Active;
    
    $user = User::create(
      $command->getId(),
      $credentials,
      $displayName,
      $status,
      $this->userCreationContext
    );

    $this->userRepository->store($user);
  }
}