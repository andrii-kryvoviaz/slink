<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\ReadModel\Repository;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Slink\Image\Domain\Repository\ImageAnalyticsRepositoryInterface;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Shared\Domain\ValueObject\Date\DateRange;
use Slink\Shared\Infrastructure\Persistence\Doctrine\Mapping\AnalyticsCountable;
use Slink\Shared\Infrastructure\Persistence\ReadModel\AbstractRepository;

final class ImageAnalyticsRepository extends AbstractRepository implements ImageAnalyticsRepositoryInterface {
  
  /**
   * @return string
   */
  static protected function entityClass(): string {
    return ImageView::class;
  }
  
  /**
   * @inheritDoc
   * @throws NoResultException|NonUniqueResultException
   */
  public function getAnalytics(DateRange $dateRange): array {
    $firstEntry = $this->getFirstImage();

    if ($firstEntry === null) {
      return [];
    }

    if($firstEntry->getAttributes()->getCreatedAt()->isAfter($dateRange->getStart())) {
      $dateRange = DateRange::create($firstEntry->getAttributes()->getCreatedAt(), $dateRange->getEnd());
    }

    $intervals = $dateRange->generateIntervals();
    $format = $dateRange->getStep()->toFormat();

    $data = [];

    foreach ($intervals as $interval) {
      $qb = $this->createQueryBuilder('i')
        ->select(sprintf('NEW %s(:start, COUNT(i.uuid))', AnalyticsCountable::class))
        ->where('i.attributes.createdAt BETWEEN :start AND :end')
        ->setParameter('start', $interval->getStart())
        ->setParameter('end', $interval->getEnd());

      $analytics = $qb->getQuery()->getSingleResult();

      $data[] = [
        'date' => $analytics->getFormattedDate($format),
        'count' => $analytics->getCount(),
      ];
    }

    return $data;
  }
  
  /**
   * @throws NonUniqueResultException
   */
  public function getFirstImage(): ?ImageView {
    return $this->createQueryBuilder('i')
      ->orderBy('i.attributes.createdAt', 'ASC')
      ->setMaxResults(1)
      ->getQuery()
      ->getOneOrNullResult();
  }
}