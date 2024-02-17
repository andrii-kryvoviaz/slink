<?php

declare(strict_types=1);

namespace Slink\User\Domain;

use Ramsey\Collection\Set;
use Slink\Shared\Domain\AbstractEventSourcedAggregate;
use Slink\Shared\Domain\Exception\DateTimeException;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\Event\Auth\RefreshTokenIssued;
use Slink\User\Domain\Event\Auth\RefreshTokenRevoked;
use Slink\User\Domain\ValueObject\Auth\HashedRefreshToken;

final class RefreshTokenSet extends AbstractEventSourcedAggregate {
  private Set $hashedRefreshTokenCollection;
  
  /**
   * @param HashedRefreshToken $hashedRefreshToken
   * @return void
   */
  private function setHashedRefreshToken(HashedRefreshToken $hashedRefreshToken): void {
    $this->hashedRefreshTokenCollection->add($hashedRefreshToken);
  }
  
  /**
   * @param ID $userId
   */
  private function __construct(private readonly ID $userId) {
    $this->hashedRefreshTokenCollection = new Set(HashedRefreshToken::class);
  }
  
  /**
   * @return void
   */
  public function clearDanglingRefreshTokens(): void {
    $this->hashedRefreshTokenCollection->map(function(HashedRefreshToken $hashedRefreshToken) {
      if ($hashedRefreshToken->isExpired()) {
        $this->revoke($hashedRefreshToken);
      }
    });
  }
  
  /**
   * @return void
   */
  public function clear(): void {
    $this->hashedRefreshTokenCollection->clear();
  }
  
  /**
   * @param ID $userId
   * @return self
   */
  public static function create(ID $userId): self {
    return new self($userId);
  }
  
  /**
   * @param HashedRefreshToken $hashedRefreshToken
   * @return void
   */
  public function issue(HashedRefreshToken $hashedRefreshToken): void {
    $this->recordThat(new RefreshTokenIssued($this->userId, $hashedRefreshToken));
  }
  
  /**
   * @param RefreshTokenIssued $event
   * @return void
   */
  public function applyRefreshTokenIssued(RefreshTokenIssued $event): void {
    $this->setHashedRefreshToken($event->hashedRefreshToken);
  }
  
  /**
   * @param HashedRefreshToken $hashedRefreshToken
   * @return void
   */
  public function revoke(HashedRefreshToken $hashedRefreshToken): void {
    $this->recordThat(new RefreshTokenRevoked($this->userId, $hashedRefreshToken));
  }
  
  /**
   * @param RefreshTokenRevoked $event
   * @return void
   */
  public function applyRefreshTokenRevoked(RefreshTokenRevoked $event): void {
    $this->hashedRefreshTokenCollection->remove($event->hashedRefreshToken);
  }
  
  /**
   * @param HashedRefreshToken $refreshTokenToRotate
   * @param HashedRefreshToken $updatedRefreshToken
   * @return void
   */
  public function rotate(HashedRefreshToken $refreshTokenToRotate, HashedRefreshToken $updatedRefreshToken): void {
    $this->revoke($refreshTokenToRotate);
    $this->issue($updatedRefreshToken);
  }
}