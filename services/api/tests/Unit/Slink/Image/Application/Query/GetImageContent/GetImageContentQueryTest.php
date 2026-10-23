<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Application\Query\GetImageContent;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Image\Application\Query\GetImageContent\GetImageContentQuery;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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

  /**
   * @return array<string, array{string}>
   */
  public static function invalidFilters(): array {
    return [
      'uppercase filter' => ['NOIR'],
      'unknown filter' => ['blur'],
    ];
  }

  /**
   * @return array<string, array{?string}>
   */
  public static function acceptedFilters(): array {
    return [
      'known filter' => ['noir'],
      'no filter' => [null],
    ];
  }

  #[Test]
  #[DataProvider('invalidFilters')]
  public function itRejectsFilterOutsideKnownFilters(string $filter): void {
    $violations = $this->validator()->validate(new GetImageContentQuery(filter: $filter));

    $this->assertCount(1, $violations);
    $this->assertSame('filter', $violations->get(0)->getPropertyPath());
  }

  #[Test]
  #[DataProvider('acceptedFilters')]
  public function itAcceptsKnownOrAbsentFilter(?string $filter): void {
    $violations = $this->validator()->validate(new GetImageContentQuery(filter: $filter));

    $this->assertCount(0, $violations);
  }

  private function validator(): ValidatorInterface {
    return Validation::createValidatorBuilder()
      ->enableAttributeMapping()
      ->getValidator();
  }
}
