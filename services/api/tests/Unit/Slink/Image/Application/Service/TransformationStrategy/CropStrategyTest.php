<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Application\Service\TransformationStrategy;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Image\Application\Service\TransformationStrategy\CropStrategy;
use Slink\Image\Domain\ValueObject\ImageDimensions;
use Slink\Image\Domain\ValueObject\ImageTransformationRequest;
use Slink\Image\Domain\ValueObject\Operation\Cover;
use Slink\Image\Domain\ValueObject\PartialImageDimensions;

final class CropStrategyTest extends TestCase {
    private CropStrategy $strategy;

    protected function setUp(): void {
        parent::setUp();

        $this->strategy = new CropStrategy();
    }

    #[Test]
    public function itSupportsCropWithTargetDimensions(): void {
        $request = new ImageTransformationRequest(
            targetDimensions: new ImageDimensions(400, 300),
            crop: true
        );

        $this->assertTrue($this->strategy->supports($request));
    }

    #[Test]
    public function itSupportsCropWithPartialDimensions(): void {
        $request = new ImageTransformationRequest(
            partialDimensions: new PartialImageDimensions(400, null),
            crop: true
        );

        $this->assertTrue($this->strategy->supports($request));
    }

    #[Test]
    public function itDoesNotSupportNonCropOperations(): void {
        $request = new ImageTransformationRequest(
            targetDimensions: new ImageDimensions(400, 300),
            crop: false
        );

        $this->assertFalse($this->strategy->supports($request));
    }

    #[Test]
    public function itDoesNotSupportRequestsWithoutDimensions(): void {
        $request = new ImageTransformationRequest(crop: true, quality: 85);

        $this->assertFalse($this->strategy->supports($request));
    }

    #[Test]
    public function itEmitsCoverWithTargetDimensions(): void {
        $request = new ImageTransformationRequest(
            targetDimensions: new ImageDimensions(400, 400),
            crop: true
        );

        $operations = $this->strategy->operations($request);

        $this->assertCount(1, $operations);
        $this->assertInstanceOf(Cover::class, $operations[0]);
        $this->assertSame(400, $operations[0]->width);
        $this->assertSame(400, $operations[0]->height);
    }

    #[Test]
    public function itEmitsCoverWithPartialSquareDimensions(): void {
        $request = new ImageTransformationRequest(
            partialDimensions: new PartialImageDimensions(400, null),
            crop: true
        );

        $operations = $this->strategy->operations($request);

        $this->assertCount(1, $operations);
        $this->assertInstanceOf(Cover::class, $operations[0]);
        $this->assertSame(400, $operations[0]->width);
        $this->assertSame(400, $operations[0]->height);
    }

    #[Test]
    public function itEmitsNoOperationsWhenNoValidDimensions(): void {
        $request = new ImageTransformationRequest(crop: true, quality: 85);

        $operations = $this->strategy->operations($request);

        $this->assertSame([], $operations);
    }
}
