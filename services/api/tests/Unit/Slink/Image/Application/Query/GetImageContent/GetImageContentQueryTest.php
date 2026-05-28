<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Application\Query\GetImageContent;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Image\Application\Query\GetImageContent\GetImageContentQuery;

final class GetImageContentQueryTest extends TestCase {
  #[Test]
  public function itDefaultsToNullTransformsAndSignature(): void {
    $query = new GetImageContentQuery();

    $this->assertNull($query->getWidth());
    $this->assertNull($query->getHeight());
    $this->assertNull($query->getQuality());
    $this->assertFalse($query->isCropped());
    $this->assertNull($query->getFormat());
    $this->assertNull($query->getFilter());
    $this->assertNull($query->getTransformSignature());
  }

  #[Test]
  public function itExposesTransformSignature(): void {
    $query = new GetImageContentQuery(width: 100, s: 'sig');

    $this->assertSame('sig', $query->getTransformSignature());
  }

  #[Test]
  public function itPreservesSignatureWhenFormatIsApplied(): void {
    $query = new GetImageContentQuery(width: 100, s: 'sig');

    $formatted = $query->withFormat('webp');

    $this->assertSame('webp', $formatted->getFormat());
    $this->assertSame('sig', $formatted->getTransformSignature());
  }

  #[Test]
  public function itExposesTransformParamsForLoader(): void {
    $query = new GetImageContentQuery(
      width: 200,
      height: 100,
      quality: 80,
      crop: true,
      filter: 'blur',
    );

    $this->assertSame(
      [
        'width' => 200,
        'height' => 100,
        'quality' => 80,
        'crop' => true,
        'filter' => 'blur',
      ],
      $query->getTransformParams(),
    );
  }
}
