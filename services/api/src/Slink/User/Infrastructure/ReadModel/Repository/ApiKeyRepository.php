<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\ReadModel\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\Repository\ApiKeyRepositoryInterface;
use Slink\User\Infrastructure\ReadModel\View\ApiKeyView;

class ApiKeyRepository extends ServiceEntityRepository implements ApiKeyRepositoryInterface {
  public function __construct(ManagerRegistry $registry) {
    parent::__construct($registry, ApiKeyView::class);
  }

  public function save(ApiKeyView $apiKey): void {
    $this->_em->persist($apiKey);
  }

  public function findByKey(string $key): ?ApiKeyView {
    $keyHash = hash('sha256', $key);
    
    /** @var ApiKeyView|null $result */
    $result = $this->findOneBy(['keyHash' => $keyHash]);
    return $result;
  }

  /**
   * @return array<int, ApiKeyView>
   */
  public function findByUserId(ID $userId): array {
    /** @var array<int, ApiKeyView> $result */
    $result = $this->findBy(['userId' => $userId->toString()], ['createdAt' => 'DESC']);
    return $result;
  }

  public function findById(string $keyId): ?ApiKeyView {
    /** @var ApiKeyView|null $result */
    $result = $this->find($keyId);
    return $result;
  }

  public function delete(ApiKeyView $apiKey): void {
    $this->_em->remove($apiKey);
  }
}
