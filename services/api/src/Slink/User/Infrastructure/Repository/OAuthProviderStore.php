<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\Repository;

use EventSauce\EventSourcing\MessageDispatcher;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractViewBackedStore;
use Slink\User\Domain\OAuthProvider;
use Slink\User\Domain\Repository\OAuthProviderRepositoryInterface;
use Slink\User\Domain\Repository\OAuthProviderStoreRepositoryInterface;
use Slink\User\Infrastructure\ReadModel\View\OAuthProviderView;

/**
 * @extends AbstractViewBackedStore<OAuthProvider, OAuthProviderView>
 */
final class OAuthProviderStore extends AbstractViewBackedStore implements OAuthProviderStoreRepositoryInterface {
  public function __construct(
    MessageDispatcher $messageDispatcher,
    private readonly OAuthProviderRepositoryInterface $providerRepository,
  ) {
    parent::__construct($messageDispatcher);
  }

  protected static function getAggregateRootClass(): string {
    return OAuthProvider::class;
  }

  protected function findRootView(ID $id): object {
    return $this->providerRepository->findById($id) ?? throw new NotFoundException();
  }

  protected function hydrate(object $view): object {
    return OAuthProvider::fromView($view);
  }

  #[\Override]
  public function get(ID $id): OAuthProvider {
    return $this->retrieve($id);
  }

  #[\Override]
  public function store(OAuthProvider $provider): void {
    $this->persist($provider);
  }
}
