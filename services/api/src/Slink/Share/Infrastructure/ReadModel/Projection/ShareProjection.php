<?php

declare(strict_types=1);

namespace Slink\Share\Infrastructure\ReadModel\Projection;

use Doctrine\ORM\EntityManagerInterface;
use Slink\Share\Domain\Event\ShareWasCreated;
use Slink\Share\Domain\Event\ShortUrlWasCreated;
use Slink\Share\Domain\Repository\ShareRepositoryInterface;
use Slink\Share\Domain\Repository\ShortUrlRepositoryInterface;
use Slink\Share\Infrastructure\ReadModel\View\ShareView;
use Slink\Share\Infrastructure\ReadModel\View\ShortUrlView;
use Slink\Shared\Domain\Event\EventWithEntityManager;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractProjection;

final class ShareProjection extends AbstractProjection {
  public function __construct(
    private readonly ShareRepositoryInterface $shareRepository,
    private readonly ShortUrlRepositoryInterface $shortUrlRepository,
    private readonly EntityManagerInterface $em,
  ) {
  }

  public function handleShareWasCreated(ShareWasCreated $event): void {
    $eventWithEntityManager = EventWithEntityManager::decorate($event, $this->em);
    $share = ShareView::fromEvent($eventWithEntityManager);

    $this->shareRepository->add($share);
    $this->em->flush();
  }

  public function handleShortUrlWasCreated(ShortUrlWasCreated $event): void {
    $share = $this->shareRepository->findById($event->shareId->toString());

    if ($share === null) {
      return;
    }

    $shortUrl = new ShortUrlView(
      $event->shortUrlId->toString(),
      $share,
      $event->shortCode,
    );

    $this->shortUrlRepository->add($shortUrl);
    $this->em->flush();
  }
}
