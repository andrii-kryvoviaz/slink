<?php

declare(strict_types=1);

namespace Slink\Comment\Infrastructure\Repository;

use EventSauce\EventSourcing\MessageDispatcher;
use EventSauce\EventSourcing\MessageRepository;
use Slink\Comment\Domain\Comment;
use Slink\Comment\Domain\Repository\CommentStoreRepositoryInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Persistence\EventStore\AbstractStoreRepository;

final class CommentStoreRepository extends AbstractStoreRepository implements CommentStoreRepositoryInterface {
  public static function getAggregateRootClass(): string {
    return Comment::class;
  }

  public function __construct(
    MessageRepository $messageRepository,
    MessageDispatcher $messageDispatcher
  ) {
    parent::__construct($messageRepository, $messageDispatcher);
  }

  public function store(Comment $comment): void {
    $this->persist($comment);
  }

  /**
   * @return Comment
   */
  public function get(ID $id): Comment {
    /** @var Comment */
    return $this->retrieve($id);
  }
}
