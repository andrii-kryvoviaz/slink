<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Persistence\ReadModel;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Slink\Shared\Infrastructure\Exception\NotFoundException;

abstract class AbstractRepository extends ServiceEntityRepository {
  /**
   * @return class-string<object>
   */
  abstract static protected function entityClass(): string;
  
  /**
   * @param ManagerRegistry $registry
   */
  public function __construct(protected ManagerRegistry $registry) {
    parent::__construct($registry, static::entityClass());
  }
  
  /**
   * @throws NonUniqueResultException
   * @throws NotFoundException
   */
  protected function oneOrException(QueryBuilder $queryBuilder, mixed $hydration = AbstractQuery::HYDRATE_OBJECT): mixed {
    $model = $queryBuilder
      ->getQuery()
      ->getOneOrNullResult($hydration);
    
    if (null === $model) {
      throw new NotFoundException();
    }
    
    return $model;
  }
}