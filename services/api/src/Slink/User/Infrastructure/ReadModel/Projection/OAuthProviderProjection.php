<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\ReadModel\Projection;

use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractProjection;
use Slink\User\Domain\Event\OAuthProvider\OAuthProviderWasCreated;
use Slink\User\Domain\Event\OAuthProvider\OAuthProviderWasUpdated;
use Slink\User\Domain\Event\OAuthProvider\OAuthProviderWasRemoved;
use Slink\User\Domain\Repository\OAuthLinkRepositoryInterface;
use Slink\User\Domain\Repository\OAuthProviderRepositoryInterface;
use Slink\User\Infrastructure\ReadModel\View\OAuthProviderView;

final class OAuthProviderProjection extends AbstractProjection {
  public function __construct(
    private readonly OAuthProviderRepositoryInterface $repository,
    private readonly OAuthLinkRepositoryInterface $linkRepository,
  ) {}

  public function handleOAuthProviderWasCreated(OAuthProviderWasCreated $event): void {
    $provider = new OAuthProviderView(
      id: (string) $event->id,
      name: (string) $event->name,
      slug: $event->slug->value,
      type: (string) $event->type,
      clientId: (string) $event->clientId,
      clientSecret: (string) $event->clientSecret,
      discoveryUrl: (string) $event->discoveryUrl,
      scopes: (string) $event->scopes,
      enabled: $event->enabled,
      sortOrder: $event->sortOrder,
    );

    $this->repository->save($provider);
  }

  public function handleOAuthProviderWasUpdated(OAuthProviderWasUpdated $event): void {
    $provider = $this->repository->findById($event->id);

    if ($provider === null) {
      return;
    }

    if ($event->name !== null) $provider->setName((string) $event->name);
    if ($event->slug !== null) $provider->setSlug($event->slug->value);
    if ($event->type !== null) $provider->setType((string) $event->type);
    if ($event->clientId !== null) $provider->setClientId((string) $event->clientId);
    if ($event->clientSecret !== null) $provider->setClientSecret((string) $event->clientSecret);
    if ($event->discoveryUrl !== null) $provider->setDiscoveryUrl((string) $event->discoveryUrl);
    if ($event->scopes !== null) $provider->setScopes((string) $event->scopes);
    if ($event->enabled !== null) $provider->setEnabled($event->enabled);
    if ($event->sortOrder !== null) $provider->setSortOrder($event->sortOrder);

    $this->repository->save($provider);
  }

  public function handleOAuthProviderWasRemoved(OAuthProviderWasRemoved $event): void {
    $provider = $this->repository->findById(ID::fromString($event->id));

    if ($provider === null) {
      return;
    }

    $this->linkRepository->deleteByProviderSlug($provider->getSlug());
    $this->repository->delete($provider);
  }
}
