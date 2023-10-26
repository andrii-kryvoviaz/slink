<?php

declare(strict_types=1);

namespace Slik\User\Infrastructure\Repository;

use EventSauce\EventSourcing\EventSourcedAggregateRootRepository;
use EventSauce\EventSourcing\MessageDispatcher;
use EventSauce\EventSourcing\MessageRepository;
use Slik\Shared\Domain\ValueObject\ID;
use Slik\User\Domain\Repository\UserStoreRepositoryInterface;
use Slik\User\Domain\User;

final class UserStore extends EventSourcedAggregateRootRepository implements UserStoreRepositoryInterface {

  public function __construct(MessageRepository $messageRepository, MessageDispatcher $messageDispatcher) {
    parent::__construct(User::class, $messageRepository, $messageDispatcher);
  }

  public function get(ID $id): User {
    return $this->retrieve($id);
  }

  public function store(User $user): void {
    $this->persist($user);
  }
}