<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\Auth;

use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\User\Domain\Enum\UserStatus;
use Slink\User\Domain\Repository\ApiKeyRepositoryInterface;
use Slink\User\Domain\Repository\UserRepositoryInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

final readonly class ApiKeyUserProvider implements UserProviderInterface {
  public function __construct(
    private ApiKeyRepositoryInterface $apiKeyRepository,
    private UserRepositoryInterface $userRepository,
  ) {}

  public function loadUserByIdentifier(string $identifier): UserInterface {
    $apiKey = $this->apiKeyRepository->findByKey($identifier);

    if ($apiKey === null || $apiKey->isExpired()) {
      throw new UserNotFoundException('API key not found or expired');
    }

    if ($this->isOwnerRestricted($apiKey->getUserId())) {
      throw new UserNotFoundException('API key owner is not active');
    }

    $apiKey->updateLastUsed();
    $this->apiKeyRepository->save($apiKey);

    return ApiKeyUser::fromApiKey($apiKey);
  }

  private function isOwnerRestricted(string $userId): bool {
    try {
      $owner = $this->userRepository->one(ID::fromString($userId));
    } catch (NotFoundException) {
      return true;
    }

    return UserStatus::from($owner->getStatus())->isRestricted();
  }

  public function refreshUser(UserInterface $user): UserInterface {
    if (!$user instanceof ApiKeyUser) {
      throw new \Symfony\Component\Security\Core\Exception\UnsupportedUserException();
    }

    return $this->loadUserByIdentifier($user->getIdentifier());
  }

  public function supportsClass(string $class): bool {
    return $class === ApiKeyUser::class;
  }
}
