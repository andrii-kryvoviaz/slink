<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Tag\Application\Query\GetTagList;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Tag\Application\Query\GetTagList\GetTagListQuery;

final class GetTagListQueryTest extends TestCase {

  #[Test]
  public function itCreatesQueryWithDefaults(): void {
    $query = new GetTagListQuery();
    
    $this->assertEquals(50, $query->getLimit());
    $this->assertEquals('name', $query->getOrderBy());
    $this->assertEquals('asc', $query->getOrder());
    $this->assertEquals(1, $query->getPage());
    $this->assertNull($query->getParentId());
    $this->assertNull($query->getSearchTerm());
    $this->assertNull($query->isRootOnly());
    $this->assertFalse($query->shouldIncludeChildren());
    $this->assertNull($query->getIds());
  }

  #[Test]
  public function itCreatesQueryWithCustomValues(): void {
    $query = new GetTagListQuery(
      limit: 25,
      orderBy: 'createdAt',
      order: 'desc',
      page: 3,
      parentId: 'parent-123',
      searchTerm: 'search term',
      rootOnly: true,
      includeChildren: true,
      ids: ['id1', 'id2']
    );
    
    $this->assertEquals(25, $query->getLimit());
    $this->assertEquals('createdAt', $query->getOrderBy());
    $this->assertEquals('desc', $query->getOrder());
    $this->assertEquals(3, $query->getPage());
    $this->assertEquals('parent-123', $query->getParentId());
    $this->assertEquals('search term', $query->getSearchTerm());
    $this->assertTrue($query->isRootOnly());
    $this->assertTrue($query->shouldIncludeChildren());
    $this->assertEquals(['id1', 'id2'], $query->getIds());
  }

  #[Test]
  #[DataProvider('validOrderByProvider')]
  public function itAcceptsValidOrderByValues(string $orderBy): void {
    $query = new GetTagListQuery(orderBy: $orderBy);
    
    $this->assertEquals($orderBy, $query->getOrderBy());
  }

  #[Test]
  #[DataProvider('validOrderProvider')]
  public function itAcceptsValidOrderValues(string $order): void {
    $query = new GetTagListQuery(order: $order);
    
    $this->assertEquals($order, $query->getOrder());
  }

  #[Test]
  public function itAcceptsValidLimitRange(): void {
    $query1 = new GetTagListQuery(limit: 1);
    $query100 = new GetTagListQuery(limit: 100);
    
    $this->assertEquals(1, $query1->getLimit());
    $this->assertEquals(100, $query100->getLimit());
  }

  #[Test]
  public function itAcceptsValidPageRange(): void {
    $query = new GetTagListQuery(page: 5);
    
    $this->assertEquals(5, $query->getPage());
  }

  #[Test]
  public function itAcceptsValidUuidAsParentId(): void {
    $validUuid = '550e8400-e29b-41d4-a716-446655440000';
    $query = new GetTagListQuery(parentId: $validUuid);
    
    $this->assertEquals($validUuid, $query->getParentId());
  }

  #[Test]
  public function itAcceptsSearchTerm(): void {
    $searchTerm = 'my search term';
    $query = new GetTagListQuery(searchTerm: $searchTerm);
    
    $this->assertEquals($searchTerm, $query->getSearchTerm());
  }

  #[Test]
  public function itAcceptsValidIds(): void {
    $ids = [
      '550e8400-e29b-41d4-a716-446655440000',
      '6ba7b810-9dad-11d1-80b4-00c04fd430c8',
      '6ba7b811-9dad-11d1-80b4-00c04fd430c8'
    ];
    $query = new GetTagListQuery(ids: $ids);
    
    $this->assertEquals($ids, $query->getIds());
  }

  #[Test]
  public function itHandlesBooleanFlags(): void {
    $query1 = new GetTagListQuery(rootOnly: true, includeChildren: false);
    $query2 = new GetTagListQuery(rootOnly: false, includeChildren: true);
    
    $this->assertTrue($query1->isRootOnly());
    $this->assertFalse($query1->shouldIncludeChildren());
    
    $this->assertFalse($query2->isRootOnly());
    $this->assertTrue($query2->shouldIncludeChildren());
  }

  /**
   * @return array<array<string>>
   */
  public static function validOrderByProvider(): array {
    return [
      ['name'],
      ['path'],
      ['createdAt'],
      ['updatedAt'],
    ];
  }

  /**
   * @return array<array<string>>
   */
  public static function validOrderProvider(): array {
    return [
      ['asc'],
      ['desc'],
    ];
  }
}