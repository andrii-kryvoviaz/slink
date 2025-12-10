<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\ReadModel\Repository;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\NonUniqueResultException;
use Slink\Image\Domain\Repository\ImageAnalyticsRepositoryInterface;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Shared\Domain\Exception\Date\DateTimeException;
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
   * @throws NonUniqueResultException
   * @throws Exception
   * @throws DateTimeException
   */
  public function getAnalytics(DateRange $dateRange): array {
    $firstEntry = $this->getFirstImage();

    if ($firstEntry === null) {
      return [];
    }

    if($firstEntry->getAttributes()->getCreatedAt()->isAfter($dateRange->getStart())) {
      $dateRange = DateRange::create($firstEntry->getAttributes()->getCreatedAt(), $dateRange->getEnd());
    }

    $step = $dateRange->getStep();
    $interval = $step->toInterval();
    $format = $step->toFormat();
    
    $timezone = new \DateTimeZone('UTC');
    $dateStart = $dateRange->getStart()->setTimezone($timezone);
    $dateEnd = $dateRange->getEnd()->setTimezone($timezone);
    
    $sql = <<<SQL
      WITH RECURSIVE all_intervals AS (
        SELECT
          :start_date AS interval_start,
          DATETIME(:start_date, :interval) AS interval_end
        WHERE :start_date <= :end_date
    
        UNION ALL
    
        SELECT
          interval_end AS interval_start,
          DATETIME(interval_end, :interval) AS interval_end
        FROM all_intervals
        WHERE interval_end < :end_date
      ),
      analytics_data AS (
        SELECT
          ai.interval_start,
          ai.interval_end,
          COUNT(i.created_at) AS image_count
        FROM all_intervals ai
        LEFT JOIN image i
          ON i.created_at >= ai.interval_start
          AND i.created_at < ai.interval_end
        GROUP BY ai.interval_start, ai.interval_end
      )
      SELECT
        interval_start AS date,
        interval_end,
        image_count AS count
      FROM analytics_data;
    SQL;
    
    $query = $this->getEntityManager()->getConnection()->prepare($sql);
    $query->bindValue('start_date', $dateStart->format('Y-m-d H:i:s'));
    $query->bindValue('end_date', $dateEnd->format('Y-m-d H:i:s'));
    $query->bindValue('interval', $interval);

    $result = $query->executeQuery()->fetchAllAssociative();
    
    return array_map(function(array $row) use ($format) {
      $analytics = AnalyticsCountable::fromPayload($row);
      
      return [
        'date' => $analytics->getFormattedDate($format),
        'count' => $analytics->getCount(),
      ];
    }, $result);
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