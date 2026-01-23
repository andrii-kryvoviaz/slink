<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Collection\Application\Query\GetCollectionCover;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Collection\Application\Query\GetCollectionCover\GetCollectionCoverQuery;

final class GetCollectionCoverQueryTest extends TestCase {
  #[Test]
  public function itStoresCollectionId(): void {
    $collectionId = 'test-collection-id';

    $query = new GetCollectionCoverQuery($collectionId);

    $this->assertEquals($collectionId, $query->getId());
  }
}
