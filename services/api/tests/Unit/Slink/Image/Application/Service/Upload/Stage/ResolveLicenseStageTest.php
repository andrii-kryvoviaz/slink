<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Application\Service\Upload\Stage;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Slink\Image\Application\Service\Upload\Stage\ResolveLicenseStage;
use Slink\Image\Domain\Enum\License;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\User\Domain\ValueObject\UserPreferences;
use Symfony\Component\HttpFoundation\File\File;
use Tests\Unit\Slink\Image\Application\Service\Upload\UploadContextFactoryTrait;

class ResolveLicenseStageTest extends TestCase {
  use UploadContextFactoryTrait;

  /**
   * @throws Exception
   */
  #[Test]
  public function itLeavesContextUnchangedWhenLicensingDisabled(): void {
    $configProvider = $this->createStub(ConfigurationProviderInterface::class);
    $configProvider->method('get')->willReturnMap([
      ['image.enableLicensing', false],
    ]);

    $stage = new ResolveLicenseStage($configProvider);

    $file = $this->createStub(File::class);
    $context = $this
      ->contextWithFile($file)
      ->withPreferences(UserPreferences::create(defaultLicense: License::CC_BY));

    $result = $stage->process($context);

    $this->assertNull($result->license());
  }

  /**
   * @throws Exception
   */
  #[Test]
  public function itAppliesPreferenceLicenseWhenLicensingEnabled(): void {
    $configProvider = $this->createStub(ConfigurationProviderInterface::class);
    $configProvider->method('get')->willReturnMap([
      ['image.enableLicensing', true],
    ]);

    $stage = new ResolveLicenseStage($configProvider);

    $file = $this->createStub(File::class);
    $context = $this
      ->contextWithFile($file)
      ->withPreferences(UserPreferences::create(defaultLicense: License::CC_BY));

    $result = $stage->process($context);

    $this->assertSame(License::CC_BY, $result->license());
  }
}
