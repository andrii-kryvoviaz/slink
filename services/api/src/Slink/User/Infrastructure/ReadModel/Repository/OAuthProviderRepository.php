<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\ReadModel\Repository;

use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractRepository;
use Slink\User\Domain\Enum\OAuthProvider;
use Slink\User\Domain\Filter\OAuthProviderFilter;
use Slink\User\Domain\Repository\OAuthProviderRepositoryInterface;
use Slink\User\Infrastructure\ReadModel\View\OAuthProviderView;

class OAuthProviderRepository extends AbstractRepository implements OAuthProviderRepositoryInterface {
  static protected function entityClass(): string {
    return OAuthProviderView::class;
  }

  public function findByProvider(OAuthProvider $provider): ?OAuthProviderView {
    /** @var OAuthProviderView|null $result */
    $result = $this->findOneBy(['slug' => $provider->value]);
    return $result;
  }

  /**
   * @return array<int, OAuthProviderView>
   */
  public function getProviders(OAuthProviderFilter $filter): array {
    $qb = $this->createQueryBuilder('p');

    if ($filter->isEnabledOnly()) {
      $qb->andWhere('p.enabled = true');
    }

    /** @var array<int, OAuthProviderView> $result */
    $result = $qb->getQuery()->getResult();
    return $result;
  }

  public function findById(ID $id): ?OAuthProviderView {
    /** @var OAuthProviderView|null $result */
    $result = $this->find($id->toString());
    return $result;
  }

  public function save(OAuthProviderView $provider): void {
    $this->getEntityManager()->persist($provider);
  }

  public function delete(OAuthProviderView $provider): void {
    $this->getEntityManager()->remove($provider);
  }
}
