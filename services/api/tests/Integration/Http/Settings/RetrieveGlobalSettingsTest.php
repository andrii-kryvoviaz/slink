<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Settings;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Test;
use Slink\Media\Domain\Enum\MediaFormat;
use Slink\Settings\Domain\Enum\SettingCategory;
use Slink\Settings\Domain\Repository\SettingsRepositoryInterface;
use Tests\Integration\Http\HttpTestCase;

final class RetrieveGlobalSettingsTest extends HttpTestCase {
  #[Test]
  public function itReturnsDefaultAllowedFormatsWhenStoredImageSettingsPredateThem(): void {
    $this->seedLegacyImageSettings();

    $adminId = $this->createUser('admin@example.com', 'admin', self::PASSWORD);
    $this->grantAdmin($adminId);
    $token = $this->login('admin', self::PASSWORD);

    $this->client->request(
      'GET',
      '/api/settings/global',
      [],
      [],
      ['HTTP_AUTHORIZATION' => 'Bearer ' . $token],
    );

    $response = $this->client->getResponse();
    self::assertSame(200, $response->getStatusCode(), (string) $response->getContent());

    /** @var array{image?: array<string, mixed>} $payload */
    $payload = \json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);

    self::assertArrayHasKey('image', $payload);
    self::assertSame('10M', $payload['image']['maxSize'] ?? null);
    self::assertSame(-1, $payload['image']['allowedFormats'] ?? null);
  }

  #[Test]
  public function itReturnsStoredStorageSettingsWithoutValidatingThem(): void {
    $this->seedLegacyStorageSettings();

    $adminId = $this->createUser('admin@example.com', 'admin', self::PASSWORD);
    $this->grantAdmin($adminId);
    $token = $this->login('admin', self::PASSWORD);

    $this->client->request(
      'GET',
      '/api/settings/global',
      [],
      [],
      ['HTTP_AUTHORIZATION' => 'Bearer ' . $token],
    );

    $response = $this->client->getResponse();
    self::assertSame(200, $response->getStatusCode(), (string) $response->getContent());

    /** @var array{storage?: array{provider?: string, adapter?: array<string, array<string, mixed>>}} $payload */
    $payload = \json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);

    self::assertArrayHasKey('storage', $payload);
    self::assertSame('local', $payload['storage']['provider'] ?? null);
    self::assertSame('/app/slink/images', $payload['storage']['adapter']['local']['dir'] ?? null);
    self::assertSame('', $payload['storage']['adapter']['s3']['bucket'] ?? null);
  }

  #[Test]
  public function itExposesMediaFormatRegistryAsResponseMetadata(): void {
    $adminId = $this->createUser('admin@example.com', 'admin', self::PASSWORD);
    $this->grantAdmin($adminId);
    $token = $this->login('admin', self::PASSWORD);

    $this->client->request(
      'GET',
      '/api/settings/global',
      [],
      [],
      ['HTTP_AUTHORIZATION' => 'Bearer ' . $token],
    );

    $response = $this->client->getResponse();
    self::assertSame(200, $response->getStatusCode(), (string) $response->getContent());

    /** @var array{image?: array<string, mixed>, meta?: array{mediaFormats?: list<array{value: string, label: string, bit: int}>}} $payload */
    $payload = \json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);

    $formats = $payload['meta']['mediaFormats'] ?? null;
    self::assertIsArray($formats);
    self::assertCount(\count(MediaFormat::cases()), $formats);

    $formatsByValue = \array_column($formats, null, 'value');
    self::assertSame(
      ['value' => 'png', 'label' => 'PNG', 'bit' => 1],
      $formatsByValue['png'] ?? null,
    );
    self::assertSame(1024, $formatsByValue['tiff']['bit'] ?? null);

    self::assertArrayHasKey('image', $payload);
    self::assertArrayNotHasKey('mediaFormats', $payload['image'] ?? []);
  }

  private function seedLegacyStorageSettings(): void {
    /** @var SettingsRepositoryInterface $repository */
    $repository = static::getContainer()->get(SettingsRepositoryInterface::class);

    $repository->setBulk([
      'storage.provider' => 'local',
      'storage.adapter.local.dir' => '/app/slink/images',
      'storage.adapter.s3.region' => '',
      'storage.adapter.s3.bucket' => '',
      'storage.adapter.s3.key' => '',
      'storage.adapter.s3.secret' => '',
    ], SettingCategory::Storage);

    /** @var EntityManagerInterface $entityManager */
    $entityManager = static::getContainer()->get(EntityManagerInterface::class);
    $entityManager->flush();
  }

  private function seedLegacyImageSettings(): void {
    /** @var SettingsRepositoryInterface $repository */
    $repository = static::getContainer()->get(SettingsRepositoryInterface::class);

    $repository->setBulk([
      'image.maxSize' => '10M',
      'image.stripExifMetadata' => true,
      'image.compressionQuality' => 71,
      'image.allowOnlyPublicImages' => false,
      'image.enableDeduplication' => true,
    ], SettingCategory::Image);

    /** @var EntityManagerInterface $entityManager */
    $entityManager = static::getContainer()->get(EntityManagerInterface::class);
    $entityManager->flush();
  }
}
