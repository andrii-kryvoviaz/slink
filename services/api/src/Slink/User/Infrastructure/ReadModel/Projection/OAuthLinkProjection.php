<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\ReadModel\Projection;

use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractProjection;
use Slink\User\Domain\Event\OAuth\OAuthAccountWasLinked;
use Slink\User\Domain\Event\OAuth\OAuthAccountWasUnlinked;
use Slink\User\Domain\Repository\OAuthLinkRepositoryInterface;
use Slink\User\Infrastructure\ReadModel\View\OAuthLinkView;

final class OAuthLinkProjection extends AbstractProjection {
  public function __construct(
    private readonly OAuthLinkRepositoryInterface $repository
  ) {}

  public function handleOAuthAccountWasLinked(OAuthAccountWasLinked $event): void {
    $link = OAuthLinkView::create(
      $event->linkId,
      $event->userId,
      $event->provider,
      $event->sub,
      $event->email,
      $event->linkedAt,
    );

    $this->repository->save($link);
  }

  public function handleOAuthAccountWasUnlinked(OAuthAccountWasUnlinked $event): void {
    $link = $this->repository->findById($event->linkId->toString());

    if ($link !== null) {
      $this->repository->delete($link);
    }
  }
}
