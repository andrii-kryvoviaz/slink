<?php

declare(strict_types=1);

namespace Tests\Integration\Slink\Image\Infrastructure\Serializer;

use PHPUnit\Framework\Attributes\Test;
use Slink\Image\Domain\ValueObject\ImageAttributes;
use Slink\Image\Domain\ValueObject\ImageMetadata;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\User\Domain\ValueObject\OAuth\SubjectId;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ImageNormalizerTest extends KernelTestCase {
  #[Test]
  public function publicImagesNormalizeToTheirPublicResourceShape(): void {
    self::assertEquals([
      'id' => 'af6068e7-90a1-4de0-9710-bf941ece5dde',
      'owner' => [
        'id' => 'guest',
        'displayName' => 'Guest',
        'username' => 'guest',
        'email' => 'guest@localhost',
      ],
      'url' => '/api/image/public/af6068e7-90a1-4de0-9710-bf941ece5dde.png',
      'attributes' => [
        'fileName' => 'photo.png',
        'description' => 'Public caption',
        'isPublic' => true,
        'createdAt' => ['formattedDate' => '2026-01-15 12:00:00', 'timestamp' => 1768478400],
        'views' => 4,
      ],
      'metadata' => ['size' => 1234, 'mimeType' => 'image/png', 'width' => 640, 'height' => 480],
      'bookmarkCount' => 3,
    ], $this->serializer()->normalize($this->image(), context: ['groups' => ['public']]));
  }

  #[Test]
  public function embeddedValuesRespectChangingGroupsAndRetainUngroupedSerialization(): void {
    $metadata = $this->image()->getMetadata();
    $serializer = $this->serializer();
    $public = ['size' => 1234, 'mimeType' => 'image/png', 'width' => 640, 'height' => 480];
    $ungrouped = ['size' => 1234, 'mime_type' => 'image/png', 'width' => 640, 'height' => 480, 'sha1_hash' => str_repeat('a', 40)];

    self::assertSame($ungrouped, $serializer->normalize($metadata));
    self::assertEquals($public, $serializer->normalize($metadata, context: ['groups' => ['public']]));
    self::assertSame([], $serializer->normalize($metadata, context: ['groups' => ['private']]));
    self::assertEquals($public, $serializer->normalize($metadata, context: ['groups' => 'public']));
    self::assertSame($ungrouped, $serializer->normalize($metadata));
    self::assertSame($ungrouped, $serializer->normalize($metadata, context: ['groups' => []]));
  }

  #[Test]
  public function restrictedImageGroupsDoNotReceivePublicFieldsOrUrls(): void {
    $image = $this->image(false);
    $serializer = $this->serializer();

    self::assertEquals(['tags' => []], $serializer->normalize($image, context: ['groups' => ['private']]));
    $public = $serializer->normalize($image, context: ['groups' => 'public']);
    self::assertIsArray($public);
    self::assertSame('/api/image/public/af6068e7-90a1-4de0-9710-bf941ece5dde.png', $public['url']);
    self::assertNull($public['metadata']);
    self::assertEquals(['tags' => []], $serializer->normalize($image, context: ['groups' => ['private']]));
    self::assertEquals($public, $serializer->normalize($image, context: ['groups' => ['public']]));
  }

  #[Test]
  public function unrelatedJsonAndExistingScalarAndDateNormalizersStayUnchanged(): void {
    $serializer = $this->serializer();
    $json = new class implements \JsonSerializable {
      public function jsonSerialize(): mixed {
        return ['legacy_name' => 'unchanged'];
      }
    };

    self::assertSame(['legacy_name' => 'unchanged'], $serializer->normalize($json, context: ['groups' => ['public']]));
    self::assertSame('external-subject', $serializer->normalize(SubjectId::fromString('external-subject')));
    self::assertSame(
      ['formattedDate' => '2026-01-15 12:00:00', 'timestamp' => 1768478400],
      $serializer->normalize(DateTime::fromString('2026-01-15T12:00:00+00:00')),
    );
  }

  private function serializer(): NormalizerInterface {
    $serializer = static::getContainer()->get(NormalizerInterface::class);
    self::assertInstanceOf(NormalizerInterface::class, $serializer);

    return $serializer;
  }

  private function image(bool $withMetadata = true): ImageView {
    $metadata = null;
    if ($withMetadata) {
      $metadata = new ImageMetadata(1234, 'image/png', 640, 480, str_repeat('a', 40));
    }

    return new ImageView(
      'af6068e7-90a1-4de0-9710-bf941ece5dde',
      null,
      ImageAttributes::create(
        'photo.png',
        'Public caption',
        true,
        DateTime::fromString('2026-01-15T12:00:00+00:00'),
        DateTime::fromString('2026-01-16T12:00:00+00:00'),
        4,
      ),
      $metadata,
      3,
    );
  }
}
