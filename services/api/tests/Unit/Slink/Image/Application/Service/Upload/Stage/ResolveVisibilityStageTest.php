<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Application\Service\Upload\Stage;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Slink\Image\Application\Service\Upload\Stage\ResolveVisibilityStage;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\Enum\DefaultVisibility;
use Slink\User\Domain\ValueObject\UserPreferences;
use Symfony\Component\HttpFoundation\File\File;
use Tests\Unit\Slink\Image\Application\Service\Upload\UploadContextFactoryTrait;

class ResolveVisibilityStageTest extends TestCase {
  use UploadContextFactoryTrait;

  /**
   * @return array<string, array{?DefaultVisibility, bool, bool, bool, bool}>
   */
  public static function visibilityMatrixProvider(): array {
    return [
      'public preference wins over requested false (owner, server off)' => [DefaultVisibility::Public, false, false, false, true],
      'private preference wins over requested true (owner, server off)' => [DefaultVisibility::Private, true, false, false, false],
      'unset falls back to requested true (owner, server off)' => [null, true, false, false, true],
      'unset falls back to requested false (owner, server off)' => [null, false, false, false, false],
      'server allowOnlyPublic forces true over private preference (owner)' => [DefaultVisibility::Private, false, true, false, true],
      'guest forces true over private preference (server off)' => [DefaultVisibility::Private, false, false, true, true],
      'guest forces true over requested false unset (server off)' => [null, false, false, true, true],
      'public preference stays true (owner, server off)' => [DefaultVisibility::Public, true, false, false, true],
    ];
  }

  /**
   * @throws Exception
   */
  #[Test]
  #[DataProvider('visibilityMatrixProvider')]
  public function itResolvesVisibility(?DefaultVisibility $preference, bool $requestedPublic, bool $allowOnlyPublic, bool $guest, bool $expected): void {
    $configProvider = $this->createStub(ConfigurationProviderInterface::class);
    $configProvider->method('get')->willReturnMap([
      ['image.allowOnlyPublicImages', $allowOnlyPublic],
    ]);

    $stage = new ResolveVisibilityStage($configProvider);

    $file = $this->createStub(File::class);
    $context = $this
      ->uploadContext($file, userId: $guest ? null : ID::generate(), requestedPublic: $requestedPublic)
      ->withPreferences(UserPreferences::create(defaultVisibility: $preference));

    $result = $stage->process($context);

    $this->assertSame($expected, $result->isPublic());
  }
}
