<?php

declare(strict_types=1);

namespace Slink\User\Application\Service;

use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\Factory\OAuthUserFactory;
use Slink\User\Domain\Repository\CheckUserByEmailInterface;
use Slink\User\Domain\Repository\OAuthLinkRepositoryInterface;
use Slink\User\Domain\Repository\UserStoreRepositoryInterface;
use Slink\User\Domain\Exception\OAuthEmailRequiredException;
use Slink\User\Domain\User;
use Slink\User\Domain\ValueObject\OAuth\OAuthClaims;

final readonly class OAuthUserResolver implements OAuthUserResolverInterface {
  public function __construct(
    private OAuthLinkRepositoryInterface $oauthLinkRepository,
    private UserStoreRepositoryInterface $userStore,
    private CheckUserByEmailInterface $checkUserByEmail,
    private OAuthUserFactory $oauthUserFactory,
  ) {}

  #[\Override]
  public function resolve(OAuthClaims $claims): User {
    $existingLink = $this->oauthLinkRepository->findBySubject($claims->getSubject());

    if ($existingLink) {
      return $this->userStore->get(ID::fromString($existingLink->getUserId()));
    }

    if (!$claims->getEmail()) {
      throw new OAuthEmailRequiredException();
    }

    $existingUserId = $this->checkUserByEmail->existsEmail($claims->getEmail());

    if (!$existingUserId) {
      return $this->oauthUserFactory->create($claims);
    }

    return $this->userStore->get(ID::fromString((string) $existingUserId));
  }
}
