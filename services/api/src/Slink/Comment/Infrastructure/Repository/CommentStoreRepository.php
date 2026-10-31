<?php

declare(strict_types=1);

namespace Slink\Comment\Infrastructure\Repository;

use EventSauce\EventSourcing\MessageDispatcher;
use Slink\Comment\Domain\Comment;
use Slink\Comment\Domain\Exception\CommentNotFoundException;
use Slink\Comment\Domain\Repository\CommentRepositoryInterface;
use Slink\Comment\Domain\Repository\CommentStoreRepositoryInterface;
use Slink\Comment\Infrastructure\ReadModel\View\CommentView;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\Shared\Infrastructure\Persistence\EventStore\AbstractViewBackedStore;

/**
 * @extends AbstractViewBackedStore<Comment, CommentView>
 */
final class CommentStoreRepository extends AbstractViewBackedStore implements CommentStoreRepositoryInterface {
  public function __construct(
    MessageDispatcher $messageDispatcher,
    private readonly CommentRepositoryInterface $commentRepository,
  ) {
    parent::__construct($messageDispatcher);
  }

  protected static function getAggregateRootClass(): string {
    return Comment::class;
  }

  protected function findRootView(ID $id): object {
    try {
      return $this->commentRepository->oneById($id->toString());
    } catch (CommentNotFoundException $exception) {
      throw new NotFoundException($exception->getMessage());
    }
  }

  protected function hydrate(object $view): object {
    return Comment::fromView($view);
  }

  public function store(Comment $comment): void {
    $this->persist($comment);
  }

  public function get(ID $id): Comment {
    return $this->retrieve($id);
  }
}
