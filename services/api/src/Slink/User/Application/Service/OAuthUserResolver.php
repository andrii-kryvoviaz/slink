<?php

declare(strict_types=1);

namespace Slink\User\Application\Service;

use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\Factory\OAuthUserFactory;
use Slink\User\Domain\Repository\CheckUserByEmailInterface;
use Slink\User\Domain\Repository\OAuthLinkRepositoryInterface;
use Slink\User\Domain\Repository\OAuthProviderRepositoryInterface;
use Slink\User\Domain\Repository\UserStoreRepositoryInterface;
use Slink\User\Domain\Exception\OAuthEmailNotVerifiedException;
use Slink\User\Domain\Exception\OAuthEmailRequiredException;
use Slink\User\Domain\User;
use Slink\User\Domain\ValueObject\OAuth\OAuthIdentity;

final readonly class OAuthUserResolver implements OAuthUserResolverInterface {
  public function __construct(
    private OAuthLinkRepositoryInterface $oauthLinkRepository,
    private UserStoreRepositoryInterface $userStore,
    private CheckUserByEmailInterface $checkUserByEmail,
    private OAuthUserFactory $oauthUserFactory,
    private OAuthProviderRepositoryInterface $providerRepository,
  ) {}

  #[\Override]
  public function resolve(OAuthIdentity $identity): User {
    $existingLink = $this->oauthLinkRepository->findBySubject($identity->getSubject());

    if ($existingLink) {
      return $this->userStore->get(ID::fromString($existingLink->getUserId()));
    }

    if (!$identity->getEmail()) {
      throw new OAuthEmailRequiredException();
    }

    $existingUserId = $this->checkUserByEmail->existsEmail($identity->getEmail());

    if (!$identity->isEmailVerified()) {
      throw new OAuthEmailNotVerifiedException();
    }

    if (!$existingUserId) {
      $provider = $this->providerRepository->findByProvider($identity->getSubject()->getProvider());

      return $this->oauthUserFactory->create($identity, $provider);
    }

    return $this->userStore->get(ID::fromString((string) $existingUserId));
  }
}
