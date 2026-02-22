<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\ReadModel\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Slink\User\Domain\Repository\OAuthLinkRepositoryInterface;
use Slink\User\Domain\ValueObject\OAuth\OAuthSubject;
use Slink\User\Infrastructure\ReadModel\View\OAuthLinkView;

class OAuthLinkRepository extends ServiceEntityRepository implements OAuthLinkRepositoryInterface {
  public function __construct(ManagerRegistry $registry) {
    parent::__construct($registry, OAuthLinkView::class);
  }

  public function findById(string $id): ?OAuthLinkView {
    /** @var OAuthLinkView|null $result */
    $result = $this->find($id);
    return $result;
  }

  public function findBySubject(OAuthSubject $subject): ?OAuthLinkView {
    /** @var OAuthLinkView|null $result */
    $result = $this->findOneBy([
      'providerSlug' => $subject->getProvider()->value,
      'providerUserId' => $subject->getSub()->toString(),
    ]);
    return $result;
  }

  /**
   * @return array<int, OAuthLinkView>
   */
  public function findByUserId(string $userId): array {
    /** @var array<int, OAuthLinkView> $result */
    $result = $this->findBy(['userId' => $userId]);
    return $result;
  }

  public function save(OAuthLinkView $link): void {
    $this->getEntityManager()->persist($link);
  }

  public function delete(OAuthLinkView $link): void {
    $this->getEntityManager()->remove($link);
  }
}
