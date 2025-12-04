<?php

declare(strict_types=1);

namespace Slink\Notification\Infrastructure\ReadModel\Repository;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Override;
use Slink\Notification\Domain\Repository\NotificationRepositoryInterface;
use Slink\Notification\Infrastructure\ReadModel\View\NotificationView;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractRepository;

final class NotificationRepository extends AbstractRepository implements NotificationRepositoryInterface {
  #[Override]
  protected static function entityClass(): string {
    return NotificationView::class;
  }

  #[Override]
  public function add(NotificationView $notification): void {
    $this->_em->persist($notification);
  }

  #[Override]
  public function oneById(string $id): NotificationView {
    $qb = $this->_em
      ->createQueryBuilder()
      ->from(NotificationView::class, 'n')
      ->select('n')
      ->where('n.uuid = :id')
      ->setParameter('id', $id);

    return $this->oneOrException($qb);
  }

  #[Override]
  public function findByUserId(string $userId, int $page = 1, int $limit = 20): Paginator {
    $qb = $this->createQueryBuilder('n')
      ->join('n.user', 'u')
      ->leftJoin('n.relatedComment', 'c')
      ->addSelect('c')
      ->where('u.uuid = :userId')
      ->setParameter('userId', $userId)
      ->orderBy('n.createdAt', 'DESC');

    return $this->paginate($qb, $page, $limit);
  }

  #[Override]
  public function countUnreadByUserId(string $userId): int {
    return (int) $this->createQueryBuilder('n')
      ->select('COUNT(n.uuid)')
      ->join('n.user', 'u')
      ->where('u.uuid = :userId')
      ->andWhere('n.isRead = :isRead')
      ->setParameter('userId', $userId)
      ->setParameter('isRead', false)
      ->getQuery()
      ->getSingleScalarResult();
  }

  #[Override]
  public function markAllAsReadByUserId(string $userId): void {
    $this->createQueryBuilder('n')
      ->update()
      ->set('n.isRead', ':isRead')
      ->set('n.readAt', ':readAt')
      ->where('n.user = :userId')
      ->andWhere('n.isRead = :notRead')
      ->setParameter('isRead', true)
      ->setParameter('readAt', new \DateTimeImmutable())
      ->setParameter('notRead', false)
      ->setParameter('userId', $userId)
      ->getQuery()
      ->execute();
  }
}
