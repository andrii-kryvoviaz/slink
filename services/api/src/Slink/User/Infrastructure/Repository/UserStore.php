<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\Repository;

use Slink\Shared\Domain\ValueObject\ID;
use EventSauce\EventSourcing\MessageDispatcher;
use Slink\Shared\Infrastructure\Persistence\EventStore\AbstractViewBackedStore;
use Slink\User\Domain\Repository\CheckUserByEmailInterface;
use Slink\User\Domain\Repository\CheckUserByRefreshTokenInterface;
use Slink\User\Domain\Repository\CheckUserByUsernameInterface;
use Slink\User\Domain\Repository\OAuthLinkRepositoryInterface;
use Slink\User\Domain\Repository\RefreshTokenRepositoryInterface;
use Slink\User\Domain\Repository\UserPreferencesRepositoryInterface;
use Slink\User\Domain\Repository\UserRepositoryInterface;
use Slink\User\Domain\Repository\UserStoreRepositoryInterface;
use Slink\User\Domain\User;
use Slink\User\Domain\ValueObject\Auth\HashedRefreshToken;
use Slink\User\Domain\ValueObject\Email;
use Slink\User\Domain\ValueObject\Username;
use Slink\User\Infrastructure\ReadModel\View\UserView;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * @extends AbstractViewBackedStore<User, UserView>
 */
final class UserStore extends AbstractViewBackedStore implements UserStoreRepositoryInterface {
  private CheckUserByEmailInterface $checkUserByEmail;
  private CheckUserByUsernameInterface $checkUserByUsername;
  private CheckUserByRefreshTokenInterface $getUserByRefreshToken;

  public function __construct(
    MessageDispatcher $messageDispatcher,
    private readonly UserRepositoryInterface $userRepository,
    private readonly OAuthLinkRepositoryInterface $oauthLinkRepository,
    private readonly UserPreferencesRepositoryInterface $userPreferencesRepository,
    private readonly RefreshTokenRepositoryInterface $refreshTokenRepository,
  ) {
    parent::__construct($messageDispatcher);
  }

  #[Required]
  public function setCheckUserByEmail(CheckUserByEmailInterface $checkUserByEmail): void {
    $this->checkUserByEmail = $checkUserByEmail;
  }
  
  #[Required]
  public function setCheckUserByUsername(CheckUserByUsernameInterface $checkUserByUsername): void {
    $this->checkUserByUsername = $checkUserByUsername;
  }
  
  #[Required]
  public function setGetUserByRefreshToken(CheckUserByRefreshTokenInterface $getUserByRefreshToken): void {
    $this->getUserByRefreshToken = $getUserByRefreshToken;
  }
  
  protected static function getAggregateRootClass(): string {
    return User::class;
  }

  protected function findRootView(ID $id): object {
    return $this->userRepository->one($id);
  }

  protected function hydrate(object $view): object {
    $userId = $view->getUuid();

    return User::fromView(
      $view,
      $this->oauthLinkRepository->findByUserId($userId),
      $this->userPreferencesRepository->findByUserId($userId),
      $this->refreshTokenRepository->findByUserId($userId),
    );
  }
  
  /**
   * @param ID $id
   * @return User
   */
  #[\Override]
  public function get(ID $id): User {
    return $this->retrieve($id);
  }
  
  /**
   * @param Email|Username $username
   * @return User|null
   */
  #[\Override]
  public function getByUsername(Email|Username $username): ?User {
    $id = $username instanceof Email
      ? $this->checkUserByEmail->existsEmail($username)
      : $this->checkUserByUsername->existsUsername($username);
    
    if ($id === null) {
      return null;
    }
    
    return $this->get(ID::fromString($id->toString()));
  }
  
  /**
   * @param HashedRefreshToken $hashedRefreshToken
   * @return User|null
   */
  #[\Override]
  public function getByRefreshToken(HashedRefreshToken $hashedRefreshToken): ?User {
    $id = $this->getUserByRefreshToken->existsByRefreshToken($hashedRefreshToken);
    if ($id === null) {
      return null;
    }
    
    return $this->get(ID::fromString($id->toString()));
  }
  
  /**
   * @param User $user
   * @return void
   */
  #[\Override]
  public function store(User $user): void {
    $user->refreshToken->clearDanglingRefreshTokens();
    
    $this->persist($user);
  }
}