<?php

declare(strict_types=1);

namespace Slink\Share\Infrastructure\Repository;

use Slink\Share\Domain\Repository\ShareStoreRepositoryInterface;
use Slink\Share\Domain\Share;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Persistence\EventStore\AbstractSnapshotStoreRepository;

final class ShareStore extends AbstractSnapshotStoreRepository implements ShareStoreRepositoryInterface {
  protected static function getAggregateRootClass(): string {
    return Share::class;
  }

  public function get(ID $id): Share {
    $share = $this->retrieve($id);
    if (!$share instanceof Share) {
      throw new \RuntimeException('Expected instance of Share, got ' . get_class($share));
    }
    return $share;
  }

  public function store(Share $share): void {
    $this->persist($share);
  }
}
