<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Domain\ValueObject;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Image\Domain\ValueObject\AnimatedImageInfo;

final class AnimatedImageInfoTest extends TestCase {
    #[Test]
    public function itCreatesStaticImageInfo(): void {
        $info = AnimatedImageInfo::static();

        $this->assertFalse($info->isAnimated());
        $this->assertEquals(1, $info->getFrameCount());
        $this->assertFalse($info->isMultiFrame());
    }

    #[Test]
    public function itCreatesAnimatedImageInfo(): void {
        $frameCount = 15;
        $info = AnimatedImageInfo::animated($frameCount);

        $this->assertTrue($info->isAnimated());
        $this->assertEquals($frameCount, $info->getFrameCount());
        $this->assertTrue($info->isMultiFrame());
    }

    #[Test]
    public function itCreatesFromPayload(): void {
        $payload = [
            'isAnimated' => true,
            'frameCount' => 10
        ];

        $info = AnimatedImageInfo::fromPayload($payload);

        $this->assertTrue($info->isAnimated());
        $this->assertEquals(10, $info->getFrameCount());
        $this->assertTrue($info->isMultiFrame());
    }

    #[Test]
    public function itCreatesFromPayloadWithDefaults(): void {
        $payload = [];

        $info = AnimatedImageInfo::fromPayload($payload);

        $this->assertFalse($info->isAnimated());
        $this->assertEquals(1, $info->getFrameCount());
        $this->assertFalse($info->isMultiFrame());
    }

    #[Test]
    public function itCreatesFromPayloadWithPartialData(): void {
        $payload = ['isAnimated' => true];

        $info = AnimatedImageInfo::fromPayload($payload);

        $this->assertTrue($info->isAnimated());
        $this->assertEquals(1, $info->getFrameCount());
        $this->assertFalse($info->isMultiFrame());
    }

    #[Test]
    public function itDetectsMultiFrameCorrectly(): void {
        $singleFrame = new AnimatedImageInfo(true, 1);
        $multiFrame = new AnimatedImageInfo(true, 5);

        $this->assertFalse($singleFrame->isMultiFrame());
        $this->assertTrue($multiFrame->isMultiFrame());
    }

    #[Test]
    public function itThrowsExceptionForInvalidFrameCount(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Frame count must be at least 1');

        new AnimatedImageInfo(true, 0);
    }

    #[Test]
    public function itThrowsExceptionForNegativeFrameCount(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Frame count must be at least 1');

        new AnimatedImageInfo(true, -1);
    }

    #[Test]
    public function itConvertsToPayload(): void {
        $info = new AnimatedImageInfo(true, 10);

        $payload = $info->toPayload();

        $this->assertEquals([
            'isAnimated' => true,
            'frameCount' => 10
        ], $payload);
    }

    #[Test]
    public function itHandlesStaticImagePayload(): void {
        $info = AnimatedImageInfo::static();

        $payload = $info->toPayload();

        $this->assertEquals([
            'isAnimated' => false,
            'frameCount' => 1
        ], $payload);
    }
}
