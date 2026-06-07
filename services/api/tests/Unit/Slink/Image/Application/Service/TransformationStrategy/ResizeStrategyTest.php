<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Application\Service\TransformationStrategy;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Image\Application\Service\TransformationStrategy\ResizeStrategy;
use Slink\Image\Domain\ValueObject\ImageDimensions;
use Slink\Image\Domain\ValueObject\ImageTransformationRequest;
use Slink\Image\Domain\ValueObject\Operation\Fit;
use Slink\Image\Domain\ValueObject\PartialImageDimensions;

final class ResizeStrategyTest extends TestCase {
    private ResizeStrategy $strategy;

    protected function setUp(): void {
        parent::setUp();

        $this->strategy = new ResizeStrategy();
    }

    #[Test]
    public function itSupportsResizeWithTargetDimensions(): void {
        $request = new ImageTransformationRequest(
            targetDimensions: new ImageDimensions(400, 300)
        );

        $this->assertTrue($this->strategy->supports($request));
    }

    #[Test]
    public function itSupportsResizeWithPartialDimensions(): void {
        $request = new ImageTransformationRequest(
            partialDimensions: new PartialImageDimensions(400, null)
        );

        $this->assertTrue($this->strategy->supports($request));
    }

    #[Test]
    public function itSupportsSmallBothDimensions(): void {
        $request = new ImageTransformationRequest(
            targetDimensions: new ImageDimensions(200, 200)
        );

        $this->assertTrue($this->strategy->supports($request));
    }

    #[Test]
    public function itEmitsFitForSmallBothDimensions(): void {
        $request = new ImageTransformationRequest(
            targetDimensions: new ImageDimensions(200, 200)
        );

        $operations = $this->strategy->operations($request);

        $this->assertCount(1, $operations);
        $this->assertInstanceOf(Fit::class, $operations[0]);
        $this->assertSame(200, $operations[0]->width);
        $this->assertSame(200, $operations[0]->height);
        $this->assertFalse($operations[0]->allowEnlarge);
    }

    #[Test]
    public function itSupportsLargeBothDimensions(): void {
        $request = new ImageTransformationRequest(
            targetDimensions: new ImageDimensions(4000, 3000)
        );

        $this->assertTrue($this->strategy->supports($request));
    }

    #[Test]
    public function itDoesNotSupportCropOperations(): void {
        $request = new ImageTransformationRequest(
            targetDimensions: new ImageDimensions(400, 300),
            crop: true
        );

        $this->assertFalse($this->strategy->supports($request));
    }

    #[Test]
    public function itDoesNotSupportRequestsWithoutDimensions(): void {
        $request = new ImageTransformationRequest(quality: 85);

        $this->assertFalse($this->strategy->supports($request));
    }

    #[Test]
    public function itEmitsFitWithTargetDimensions(): void {
        $request = new ImageTransformationRequest(targetDimensions: new ImageDimensions(400, 300));

        $operations = $this->strategy->operations($request);

        $this->assertCount(1, $operations);
        $this->assertInstanceOf(Fit::class, $operations[0]);
        $this->assertSame(400, $operations[0]->width);
        $this->assertSame(300, $operations[0]->height);
        $this->assertFalse($operations[0]->allowEnlarge);
    }

    #[Test]
    public function itEmitsFitWithPartialWidthOnly(): void {
        $request = new ImageTransformationRequest(partialDimensions: new PartialImageDimensions(400, null));

        $operations = $this->strategy->operations($request);

        $this->assertCount(1, $operations);
        $this->assertInstanceOf(Fit::class, $operations[0]);
        $this->assertSame(400, $operations[0]->width);
        $this->assertNull($operations[0]->height);
    }

    #[Test]
    public function itEmitsFitWithPartialHeightOnly(): void {
        $request = new ImageTransformationRequest(partialDimensions: new PartialImageDimensions(null, 300));

        $operations = $this->strategy->operations($request);

        $this->assertCount(1, $operations);
        $this->assertInstanceOf(Fit::class, $operations[0]);
        $this->assertNull($operations[0]->width);
        $this->assertSame(300, $operations[0]->height);
    }

    #[Test]
    public function itPropagatesEnlargeFlag(): void {
        $request = new ImageTransformationRequest(
            targetDimensions: new ImageDimensions(400, 300),
            allowEnlarge: true
        );

        $operations = $this->strategy->operations($request);

        $this->assertCount(1, $operations);
        $this->assertInstanceOf(Fit::class, $operations[0]);
        $this->assertTrue($operations[0]->allowEnlarge);
    }

    #[Test]
    public function itEmitsNoOperationsWhenNoValidDimensions(): void {
        $request = new ImageTransformationRequest(quality: 85);

        $operations = $this->strategy->operations($request);

        $this->assertSame([], $operations);
    }
}
