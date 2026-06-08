<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Application\Service\TransformationStrategy;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Image\Application\Service\TransformationStrategy\FilterStrategy;
use Slink\Image\Domain\Enum\ImageFilter;
use Slink\Image\Domain\ValueObject\ImageTransformationRequest;
use Slink\Image\Domain\ValueObject\Operation\Filter;

final class FilterStrategyTest extends TestCase {
    private FilterStrategy $strategy;

    protected function setUp(): void {
        parent::setUp();

        $this->strategy = new FilterStrategy();
    }

    #[Test]
    public function itSupportsRequestsWithFilter(): void {
        $request = new ImageTransformationRequest(filter: 'sepia');

        $this->assertTrue($this->strategy->supports($request));
    }

    #[Test]
    public function itDoesNotSupportRequestsWithoutFilter(): void {
        $request = new ImageTransformationRequest();

        $this->assertFalse($this->strategy->supports($request));
    }

    #[Test]
    public function itEmitsFilterOperation(): void {
        $request = new ImageTransformationRequest(filter: 'sepia');

        $operations = $this->strategy->operations($request);

        $this->assertCount(1, $operations);
        $this->assertInstanceOf(Filter::class, $operations[0]);
        $this->assertSame(ImageFilter::Sepia, $operations[0]->getFilter());
    }

    #[Test]
    public function itEmitsNoOperationsWhenFilterIsNull(): void {
        $request = new ImageTransformationRequest();

        $operations = $this->strategy->operations($request);

        $this->assertSame([], $operations);
    }
}
