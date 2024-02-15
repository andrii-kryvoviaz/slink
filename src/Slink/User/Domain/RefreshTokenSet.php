<?php

declare(strict_types=1);

namespace Slink\User\Domain;

use Ramsey\Collection\Set;
use Slink\Shared\Domain\AbstractEventSourcedAggregate;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\Event\Auth\RefreshTokenIssued;
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
}