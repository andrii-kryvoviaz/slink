<?php

declare(strict_types=1);

namespace Slink\Tag\Infrastructure\Repository;

use EventSauce\EventSourcing\MessageDispatcher;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Persistence\EventStore\AbstractViewBackedStore;
use Slink\Tag\Domain\Repository\TagRepositoryInterface;
use Slink\Tag\Domain\Repository\TagStoreRepositoryInterface;
use Slink\Tag\Domain\Tag;
use Slink\Tag\Infrastructure\ReadModel\View\TagView;

/**
 * @extends AbstractViewBackedStore<Tag, TagView>
 */
final class TagStore extends AbstractViewBackedStore implements TagStoreRepositoryInterface {
  public function __construct(
    MessageDispatcher $messageDispatcher,
    private readonly TagRepositoryInterface $tagRepository,
  ) {
    parent::__construct($messageDispatcher);
  }

  protected static function getAggregateRootClass(): string {
    return Tag::class;
  }

  protected function findRootView(ID $id): object {
    return $this->tagRepository->oneById($id);
  }

  protected function hydrate(object $view): object {
    return Tag::fromView($view);
  }

  public function get(ID $id): Tag {
    return $this->retrieve($id);
  }

  public function store(Tag $tag): void {
    $this->persist($tag);
  }
}
