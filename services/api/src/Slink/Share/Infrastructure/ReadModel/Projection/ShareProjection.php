<?php

declare(strict_types=1);

namespace Slink\Share\Infrastructure\ReadModel\Projection;

use Slink\Share\Domain\Event\ShareWasCreated;
use Slink\Share\Domain\Event\ShortUrlWasAdded;
use Slink\Share\Domain\Repository\ShareRepositoryInterface;
use Slink\Share\Domain\Repository\ShortUrlRepositoryInterface;
use Slink\Share\Infrastructure\ReadModel\View\ShareView;
use Slink\Share\Infrastructure\ReadModel\View\ShortUrlView;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractProjection;

final class ShareProjection extends AbstractProjection {
  public function __construct(
    private readonly ShareRepositoryInterface $shareRepository,
    private readonly ShortUrlRepositoryInterface $shortUrlRepository,
  ) {
  }

  public function handleShareWasCreated(ShareWasCreated $event): void {
    $share = new ShareView(
      $event->id->toString(),
      $event->shareable,
      $event->targetUrl,
      $event->createdAt,
    );

    $this->shareRepository->add($share);

    if ($event->context->hasShortUrl()) {
      $this->createShortUrl($share, $event->context->getShortUrlId()?->toString(), $event->context->getShortCode());
    }
  }

  public function handleShortUrlWasAdded(ShortUrlWasAdded $event): void {
    $share = $this->shareRepository->findById($event->shareId->toString());

    if ($share === null) {
      return;
    }

    $this->createShortUrl($share, $event->shortUrlId->toString(), $event->shortCode);
  }

  private function createShortUrl(ShareView $share, ?string $shortUrlId, ?string $shortCode): void {
    if ($shortUrlId === null || $shortCode === null) {
      return;
    }

    $shortUrl = new ShortUrlView($shortUrlId, $share, $shortCode);
    $this->shortUrlRepository->add($shortUrl);
  }
}
