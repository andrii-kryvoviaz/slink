<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Domain\Filter;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Image\Domain\Filter\ImageListFilter;

final class ImageListFilterTest extends TestCase {
    #[Test]
    public function itCreatesWithDefaults(): void {
        $filter = new ImageListFilter();

        $this->assertEquals(10, $filter->getLimit());
        $this->assertEquals('attributes.createdAt', $filter->getOrderBy());
        $this->assertEquals('desc', $filter->getOrder());
        $this->assertNull($filter->getIsPublic());
        $this->assertNull($filter->getUserId());
        $this->assertEquals([], $filter->getUuids());
        $this->assertNull($filter->getSearchTerm());
        $this->assertNull($filter->getSearchBy());
        $this->assertNull($filter->getCursor());
    }

    #[Test]
    public function itCreatesWithCustomValues(): void {
        $filter = new ImageListFilter(
            limit: 20,
            orderBy: 'attributes.fileName',
            order: 'asc',
            isPublic: true,
            userId: 'user-123',
            uuids: ['uuid1', 'uuid2'],
            searchTerm: 'sunset',
            searchBy: 'description',
            cursor: 'cursor-token'
        );

        $this->assertEquals(20, $filter->getLimit());
        $this->assertEquals('attributes.fileName', $filter->getOrderBy());
        $this->assertEquals('asc', $filter->getOrder());
        $this->assertTrue($filter->getIsPublic());
        $this->assertEquals('user-123', $filter->getUserId());
        $this->assertEquals(['uuid1', 'uuid2'], $filter->getUuids());
        $this->assertEquals('sunset', $filter->getSearchTerm());
        $this->assertEquals('description', $filter->getSearchBy());
        $this->assertEquals('cursor-token', $filter->getCursor());
    }

    #[Test]
    public function itHandlesNullValues(): void {
        $filter = new ImageListFilter(
            limit: null,
            orderBy: null,
            order: null,
            isPublic: null,
            userId: null,
            uuids: null,
            searchTerm: null,
            searchBy: null,
            cursor: null
        );

        $this->assertNull($filter->getLimit());
        $this->assertNull($filter->getOrderBy());
        $this->assertNull($filter->getOrder());
        $this->assertNull($filter->getIsPublic());
        $this->assertNull($filter->getUserId());
        $this->assertNull($filter->getUuids());
        $this->assertNull($filter->getSearchTerm());
        $this->assertNull($filter->getSearchBy());
        $this->assertNull($filter->getCursor());
    }

    #[Test]
    public function itHandlesPublicImagesFilter(): void {
        $publicFilter = new ImageListFilter(isPublic: true);
        $privateFilter = new ImageListFilter(isPublic: false);

        $this->assertTrue($publicFilter->getIsPublic());
        $this->assertFalse($privateFilter->getIsPublic());
    }

    #[Test]
    public function itHandlesUserSpecificFilter(): void {
        $userId = 'user-abc-123';
        $filter = new ImageListFilter(userId: $userId);

        $this->assertEquals($userId, $filter->getUserId());
    }

    #[Test]
    public function itHandlesUuidsFilter(): void {
        $uuids = ['uuid-1', 'uuid-2', 'uuid-3'];
        $filter = new ImageListFilter(uuids: $uuids);

        $this->assertEquals($uuids, $filter->getUuids());
    }

    #[Test]
    public function itHandlesEmptyUuidsArray(): void {
        $filter = new ImageListFilter(uuids: []);

        $this->assertEquals([], $filter->getUuids());
    }

    #[Test]
    public function itHandlesSearchFilters(): void {
        $filter = new ImageListFilter(
            searchTerm: 'nature photography',
            searchBy: 'description'
        );

        $this->assertEquals('nature photography', $filter->getSearchTerm());
        $this->assertEquals('description', $filter->getSearchBy());
    }

    #[Test]
    public function itHandlesDifferentSearchByOptions(): void {
        $userFilter = new ImageListFilter(searchBy: 'user');
        $hashtagFilter = new ImageListFilter(searchBy: 'hashtag');
        $descriptionFilter = new ImageListFilter(searchBy: 'description');

        $this->assertEquals('user', $userFilter->getSearchBy());
        $this->assertEquals('hashtag', $hashtagFilter->getSearchBy());
        $this->assertEquals('description', $descriptionFilter->getSearchBy());
    }

    #[Test]
    public function itHandlesDifferentOrderByOptions(): void {
        $createdAtFilter = new ImageListFilter(orderBy: 'attributes.createdAt');
        $fileNameFilter = new ImageListFilter(orderBy: 'attributes.fileName');
        $viewsFilter = new ImageListFilter(orderBy: 'attributes.views');

        $this->assertEquals('attributes.createdAt', $createdAtFilter->getOrderBy());
        $this->assertEquals('attributes.fileName', $fileNameFilter->getOrderBy());
        $this->assertEquals('attributes.views', $viewsFilter->getOrderBy());
    }

    #[Test]
    public function itHandlesDifferentOrderDirections(): void {
        $ascFilter = new ImageListFilter(order: 'asc');
        $descFilter = new ImageListFilter(order: 'desc');

        $this->assertEquals('asc', $ascFilter->getOrder());
        $this->assertEquals('desc', $descFilter->getOrder());
    }

    #[Test]
    public function itHandlesCursorPagination(): void {
        $cursor = 'eyJjcmVhdGVkQXQiOiIyMDI0LTAxLTAxIiwiaWQiOiJ1dWlkLTEyMyJ9';
        $filter = new ImageListFilter(cursor: $cursor);

        $this->assertEquals($cursor, $filter->getCursor());
    }
}
