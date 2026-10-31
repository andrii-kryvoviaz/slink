<?php

declare(strict_types=1);

namespace Slink\Bookmark\Infrastructure\Repository;

use EventSauce\EventSourcing\MessageDispatcher;
use Slink\Bookmark\Domain\Bookmark;
use Slink\Bookmark\Domain\Repository\BookmarkRepositoryInterface;
use Slink\Bookmark\Domain\Repository\BookmarkStoreRepositoryInterface;
use Slink\Bookmark\Infrastructure\ReadModel\View\BookmarkView;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Persistence\EventStore\AbstractViewBackedStore;

/**
 * @extends AbstractViewBackedStore<Bookmark, BookmarkView>
 */
final class BookmarkStoreRepository extends AbstractViewBackedStore implements BookmarkStoreRepositoryInterface {
  public function __construct(
    MessageDispatcher $messageDispatcher,
    private readonly BookmarkRepositoryInterface $bookmarkRepository,
  ) {
    parent::__construct($messageDispatcher);
  }

  protected static function getAggregateRootClass(): string {
    return Bookmark::class;
  }

  protected function findRootView(ID $id): object {
    return $this->bookmarkRepository->oneById($id->toString());
  }

  protected function hydrate(object $view): object {
    return Bookmark::fromView($view);
  }

  public function store(Bookmark $bookmark): void {
    $this->persist($bookmark);
  }

  public function get(ID $id): Bookmark {
    return $this->retrieve($id);
  }
}
