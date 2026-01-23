<?php

declare(strict_types=1);

namespace Slink\Share\Infrastructure\ReadModel\Projection;

use Slink\Share\Domain\Event\ShareWasCreated;
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
      $shortUrlId = $event->context->getShortUrlId();
      $shortCode = $event->context->getShortCode();
      
      if ($shortUrlId !== null && $shortCode !== null) {
        $shortUrl = new ShortUrlView(
          $shortUrlId->toString(),
          $share,
          $shortCode,
        );

        $this->shortUrlRepository->add($shortUrl);
      }
    }
  }
}
