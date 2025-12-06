<?php

declare(strict_types=1);

namespace Slink\Share\Infrastructure\ReadModel\Projection;

use Doctrine\ORM\EntityManagerInterface;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
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
    private readonly EntityManagerInterface $em,
  ) {
  }

  public function handleShareWasCreated(ShareWasCreated $event): void {
    /** @var ImageView $image */
    $image = $this->em->getReference(
      ImageView::class,
      $event->imageId->toString()
    );

    $share = new ShareView(
      $event->id->toString(),
      $image,
      $event->targetUrl,
      $event->createdAt,
    );

    $this->shareRepository->add($share);

    if ($event->context->hasShortUrl()) {
      $shortUrl = new ShortUrlView(
        (string) $event->context->getShortUrlId(),
        $share,
        $event->context->getShortCode(),
      );

      $this->shortUrlRepository->add($shortUrl);
    }
  }
}
