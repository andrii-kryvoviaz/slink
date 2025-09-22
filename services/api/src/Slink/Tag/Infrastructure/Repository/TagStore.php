<?php

declare(strict_types=1);

namespace Slink\Tag\Infrastructure\Repository;

use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Persistence\EventStore\AbstractSnapshotStoreRepository;
use Slink\Tag\Domain\Repository\TagStoreRepositoryInterface;
use Slink\Tag\Domain\Tag;

final class TagStore extends AbstractSnapshotStoreRepository implements TagStoreRepositoryInterface {
  protected static function getAggregateRootClass(): string {
    return Tag::class;
  }

  public function get(ID $id): Tag {
    $tag = $this->retrieve($id);
    if (!$tag instanceof Tag) {
      throw new \RuntimeException('Expected instance of Tag, got ' . get_class($tag));
    }
    return $tag;
  }

  public function store(Tag $tag): void {
    $this->persist($tag);
  }
}