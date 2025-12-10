<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\ReadModel\Repository;

use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractRepository;
use Slink\User\Domain\Repository\UserAnalyticsRepositoryInterface;
use Slink\User\Infrastructure\ReadModel\View\UserView;

final class UserAnalyticsRepository extends AbstractRepository implements UserAnalyticsRepositoryInterface {
  /**
   * @return string
   */
  static protected function entityClass(): string {
    return UserView::class;
  }
  
  /**
   * @inheritDoc
   */
  public function getAnalytics(): array {
    $result = $this->getEntityManager()
      ->createQueryBuilder()
      ->from(UserView::class, 'user')
      ->select('user.status, COUNT(user.uuid) as count')
      ->groupBy('user.status')
      ->getQuery()
      ->getResult();
    
    return array_reduce($result, function($carry, $item) {
      $status = $item['status'];
      $carry[$status->value] = $item['count'];
      return $carry;
    }, []);
  }
}