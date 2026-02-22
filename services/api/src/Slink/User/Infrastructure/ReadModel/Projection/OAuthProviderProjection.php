<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\ReadModel\Projection;

use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractProjection;
use Slink\User\Domain\Event\OAuthProvider\OAuthProviderWasCreated;
use Slink\User\Domain\Event\OAuthProvider\OAuthProviderWasUpdated;
use Slink\User\Domain\Event\OAuthProvider\OAuthProviderWasRemoved;
use Slink\User\Domain\Repository\OAuthProviderRepositoryInterface;
use Slink\User\Infrastructure\ReadModel\View\OAuthProviderView;

final class OAuthProviderProjection extends AbstractProjection {
  public function __construct(
    private readonly OAuthProviderRepositoryInterface $repository
  ) {}

  public function handleOAuthProviderWasCreated(OAuthProviderWasCreated $event): void {
    $provider = new OAuthProviderView(
      id: $event->id,
      name: $event->name,
      slug: $event->slug,
      type: $event->type,
      clientId: $event->clientId,
      clientSecret: $event->clientSecret,
      discoveryUrl: $event->discoveryUrl,
      scopes: $event->scopes,
      enabled: $event->enabled,
    );

    $this->repository->save($provider);
  }

  public function handleOAuthProviderWasUpdated(OAuthProviderWasUpdated $event): void {
    $provider = $this->repository->findById(ID::fromString($event->id));

    if ($provider === null) {
      return;
    }

    if ($event->name !== null) $provider->setName($event->name);
    if ($event->slug !== null) $provider->setSlug($event->slug);
    if ($event->type !== null) $provider->setType($event->type);
    if ($event->clientId !== null) $provider->setClientId($event->clientId);
    if ($event->clientSecret !== null) $provider->setClientSecret($event->clientSecret);
    if ($event->discoveryUrl !== null) $provider->setDiscoveryUrl($event->discoveryUrl);
    if ($event->scopes !== null) $provider->setScopes($event->scopes);
    if ($event->enabled !== null) $provider->setEnabled($event->enabled);

    $this->repository->save($provider);
  }

  public function handleOAuthProviderWasRemoved(OAuthProviderWasRemoved $event): void {
    $provider = $this->repository->findById(ID::fromString($event->id));

    if ($provider !== null) {
      $this->repository->delete($provider);
    }
  }
}
