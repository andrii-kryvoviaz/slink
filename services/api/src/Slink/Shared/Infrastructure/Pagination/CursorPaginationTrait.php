<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Pagination;

use Doctrine\ORM\QueryBuilder;
use Slink\Shared\Domain\Contract\CursorAwareInterface;
use Slink\Shared\Domain\ValueObject\Cursor;
use InvalidArgumentException;

trait CursorPaginationTrait {
  /**
   * @param QueryBuilder $qb
   * @param string $encodedCursor
   * @param string $orderByField
   * @param string $order
   * @param string $idField
   * @param string $entityAlias
   * @return void
   */
  protected function applyCursorPagination(
    QueryBuilder $qb,
    string       $encodedCursor,
    string       $orderByField,
    string       $order = 'desc',
    string       $idField = 'uuid',
    string       $entityAlias = 'entity'
  ): void {
    $cursor = Cursor::fromEncodedString($encodedCursor);

    if (!$cursor) {
      return;
    }

    $this->applyCursorCondition($qb, $cursor, $orderByField, $order, $idField, $entityAlias);
  }

  /**
   * @param QueryBuilder $qb
   * @param string $cursorString
   * @param string $orderByField
   * @param string $order
   * @param string $idField
   * @param string $entityAlias
   * @return void
   */
  protected function applyCursorPaginationFlexible(
    QueryBuilder $qb,
    string       $cursorString,
    string       $orderByField,
    string       $order = 'desc',
    string       $idField = 'uuid',
    string       $entityAlias = 'entity'
  ): void {
    $cursor = Cursor::fromJsonString($cursorString) ?? Cursor::fromEncodedString($cursorString);

    if (!$cursor) {
      return;
    }

    $this->applyCursorCondition($qb, $cursor, $orderByField, $order, $idField, $entityAlias);
  }

  /**
   * @param CursorAwareInterface $lastEntity
   * @param bool $useEncoding
   * @return string|null
   * @throws \JsonException
   */
  protected function generateNextCursor(
    CursorAwareInterface $lastEntity,
    bool                 $useEncoding = true
  ): ?string {
    try {
      $cursor = Cursor::fromEntityData(
        $lastEntity->getCursorTimestamp(),
        $lastEntity->getCursorId()
      );
      return $useEncoding ? $cursor->encode() : $cursor->toJson();
    } catch (InvalidArgumentException) {
      return null;
    }
  }

  private function applyCursorCondition(
    QueryBuilder $qb,
    Cursor       $cursor,
    string       $orderByField,
    string       $order,
    string       $idField,
    string       $entityAlias
  ): void {
    $cursorDateTime = $cursor->toDateTime();
    if (!$cursorDateTime) {
      return;
    }

    $operator = strtolower($order) === 'desc' ? '<' : '>';
    $qb->andWhere(
      "({$entityAlias}.{$orderByField} {$operator} :cursorTimestamp) OR 
             ({$entityAlias}.{$orderByField} = :cursorTimestamp AND {$entityAlias}.{$idField} {$operator} :cursorId)"
    );

    $qb->setParameter('cursorTimestamp', $cursorDateTime)
      ->setParameter('cursorId', $cursor->id);
  }
}
