<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\Auth;

use Slink\User\Domain\Repository\ApiKeyRepositoryInterface;
use Slink\User\Infrastructure\ReadModel\View\ApiKeyView;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

final readonly class ApiKeyUserProvider implements UserProviderInterface {
  public function __construct(
    private ApiKeyRepositoryInterface $apiKeyRepository
  ) {}

  public function loadUserByIdentifier(string $identifier): UserInterface {
    $apiKey = $this->apiKeyRepository->findByKey($identifier);
    
    if ($apiKey === null || $apiKey->isExpired()) {
      throw new \Symfony\Component\Security\Core\Exception\UserNotFoundException('API key not found or expired');
    }

    $apiKey->updateLastUsed();
    $this->apiKeyRepository->save($apiKey);

    return ApiKeyUser::fromApiKey($apiKey);
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
