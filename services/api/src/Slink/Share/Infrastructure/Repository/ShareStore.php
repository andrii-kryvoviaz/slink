<?php

declare(strict_types=1);

namespace Slink\Share\Infrastructure\Repository;

use Slink\Share\Domain\Repository\ShareRepositoryInterface;
use Slink\Share\Domain\Repository\ShareStoreRepositoryInterface;
use Slink\Share\Domain\Share;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Persistence\EventStore\AbstractSnapshotStoreRepository;
use Slink\Shared\Infrastructure\Persistence\EventStore\SnapshotRepositoryFactory;
use EventSauce\EventSourcing\Snapshotting\SnapshotRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class ShareStore extends AbstractSnapshotStoreRepository implements ShareStoreRepositoryInterface {
  public function __construct(
    SnapshotRepositoryFactory $factory,
    SnapshotRepository $snapshotRepository,
    #[Autowire(param: 'event_sauce.snapshot_frequency')]
    int $snapshotFrequency,
    private readonly ShareRepositoryInterface $shareRepository,
  ) {
    parent::__construct($factory, $snapshotRepository, $snapshotFrequency);
  }

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

  public function findByTargetUrl(string $targetUrl): ?Share {
    $shareView = $this->shareRepository->findByTargetUrl($targetUrl);
    if ($shareView === null) {
      return null;
    }
    return $this->get(ID::fromString($shareView->getId()));
  }

  public function store(Share $share): void {
    $this->persist($share);
  }
}
