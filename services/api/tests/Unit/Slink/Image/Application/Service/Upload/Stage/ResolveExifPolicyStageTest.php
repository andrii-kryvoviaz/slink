<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Application\Service\Upload\Stage;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Slink\Image\Application\Service\Upload\Stage\ResolveExifPolicyStage;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\User\Domain\Enum\ExifMetadataPreference;
use Slink\User\Domain\ValueObject\UserPreferences;
use Symfony\Component\HttpFoundation\File\File;
use Tests\Unit\Slink\Image\Application\Service\Upload\UploadContextFactoryTrait;

class ResolveExifPolicyStageTest extends TestCase {
  use UploadContextFactoryTrait;

  /**
   * @return array<string, array{ExifMetadataPreference, bool, bool}>
   */
  public static function exifPolicyMatrixProvider(): array {
    return [
      'keep override, server strip' => [ExifMetadataPreference::Keep, true, false],
      'keep override, server keep' => [ExifMetadataPreference::Keep, false, false],
      'strip override, server strip' => [ExifMetadataPreference::Strip, true, true],
      'strip override, server keep' => [ExifMetadataPreference::Strip, false, true],
      'default follows server strip' => [ExifMetadataPreference::Default, true, true],
      'default follows server keep' => [ExifMetadataPreference::Default, false, false],
    ];
  }

  /**
   * @throws Exception
   */
  #[Test]
  #[DataProvider('exifPolicyMatrixProvider')]
  public function itResolvesStripExifMetadataPolicy(ExifMetadataPreference $preference, bool $serverStrip, bool $expected): void {
    $configProvider = $this->createStub(ConfigurationProviderInterface::class);
    $configProvider->method('get')->willReturnMap([
      ['image.stripExifMetadata', $serverStrip],
    ]);

    $stage = new ResolveExifPolicyStage($configProvider);

    $file = $this->createStub(File::class);
    $preferences = UserPreferences::create(exifMetadataPreference: $preference);
    $context = $this->contextWithPreferences($file, $preferences);

    $result = $stage->process($context);

    $this->assertSame($expected, $result->stripExifMetadata());
  }
}
