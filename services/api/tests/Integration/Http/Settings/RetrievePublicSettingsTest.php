<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Settings;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Test;
use Slink\Media\Domain\Enum\MediaFormat;
use Slink\Settings\Domain\Enum\SettingCategory;
use Slink\Settings\Domain\Repository\SettingsRepositoryInterface;
use Tests\Integration\Http\HttpTestCase;

final class RetrievePublicSettingsTest extends HttpTestCase {
  #[Test]
  public function itExposesAllowedFormatLabelsForRestrictedMask(): void {
    $this->seedAllowedFormats(MediaFormat::Png->bit() | MediaFormat::Jpeg->bit());

    $image = $this->requestPublicImageSettings();

    self::assertSame(['PNG', 'JPEG'], $image['allowedFormatLabels'] ?? null);
    self::assertSame(['image/png', 'image/jpeg', 'image/jpg'], $image['allowedMimeTypes'] ?? null);
  }

  #[Test]
  public function itExposesAllFormatLabelsByDefaultWithoutLeakingTheMask(): void {
    $image = $this->requestPublicImageSettings();

    $expectedLabels = array_map(
      static fn (MediaFormat $format): string => $format->label(),
      MediaFormat::cases(),
    );

    self::assertSame($expectedLabels, $image['allowedFormatLabels'] ?? null);
    self::assertArrayNotHasKey('allowedFormats', $image);
    self::assertMatchesRegularExpression('/^\d+[kM]$/', $image['maxSize'] ?? '');
  }

  /**
   * @return array<string, mixed>
   */
  private function requestPublicImageSettings(): array {
    $this->client->request('GET', '/api/settings/public');

    $response = $this->client->getResponse();
    self::assertSame(200, $response->getStatusCode(), (string) $response->getContent());

    /** @var array{data?: array{image?: array<string, mixed>}, image?: array<string, mixed>} $payload */
    $payload = \json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);

    $image = $payload['data']['image'] ?? $payload['image'] ?? null;
    self::assertIsArray($image);

    return $image;
  }

  private function seedAllowedFormats(int $mask): void {
    /** @var SettingsRepositoryInterface $repository */
    $repository = static::getContainer()->get(SettingsRepositoryInterface::class);

    $repository->setBulk([
      'image.allowedFormats' => $mask,
    ], SettingCategory::Image);

    /** @var EntityManagerInterface $entityManager */
    $entityManager = static::getContainer()->get(EntityManagerInterface::class);
    $entityManager->flush();
  }
}
