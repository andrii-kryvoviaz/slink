<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Tag\Application\Query\GetImageTags;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Tag\Application\Query\GetImageTags\GetImageTagsQuery;

final class GetImageTagsQueryTest extends TestCase {

  #[Test]
  public function itCreatesQueryWithImageId(): void {
    $imageId = '550e8400-e29b-41d4-a716-446655440000';

    $query = new GetImageTagsQuery($imageId);

    $this->assertEquals($imageId, $query->getImageId());
  }

  #[Test]
  public function itImplementsQueryInterface(): void {
    $imageId = '660e8400-e29b-41d4-a716-446655440000';

    $query = new GetImageTagsQuery($imageId);

    $this->assertInstanceOf(\Slink\Shared\Application\Query\QueryInterface::class, $query);
  }

  #[Test]
  public function itStoresImageIdCorrectly(): void {
    $imageId = '770e8400-e29b-41d4-a716-446655440000';

    $query = new GetImageTagsQuery($imageId);

    $this->assertEquals($imageId, $query->getImageId());
  }
}