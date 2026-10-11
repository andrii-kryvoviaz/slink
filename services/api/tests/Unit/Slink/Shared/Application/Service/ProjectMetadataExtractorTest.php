<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Shared\Application\Service;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Application\Service\ProjectMetadataExtractor;
use Symfony\Component\HttpKernel\KernelInterface;

final class ProjectMetadataExtractorTest extends TestCase {
  private string $_scratch;

  protected function setUp(): void {
    $this->_scratch = \sys_get_temp_dir() . '/slink_project_metadata_' . \bin2hex(\random_bytes(6));
    \mkdir($this->_scratch . '/api', 0777, true);
  }

  protected function tearDown(): void {
    foreach (['client/svelte-kit/package.json', 'client/package.json'] as $file) {
      @\unlink($this->_scratch . '/' . $file);
    }

    foreach (['client/svelte-kit', 'client', 'api', ''] as $dir) {
      @\rmdir($this->_scratch . '/' . $dir);
    }
  }

  #[Test]
  #[Group('sc-900')]
  public function itReturnsInjectedVersionIgnoringPackageJsonVersion(): void {
    $this->writePackageJson('client/svelte-kit/package.json', ['version' => '9.9.9']);

    $extractor = new ProjectMetadataExtractor($this->kernel('test'), $this->configurationProvider(), '1.15.0');

    self::assertSame('1.15.0', $extractor->version());
  }

  #[Test]
  #[Group('sc-900')]
  public function itReturnsInjectedVersionWhenPackageJsonHasNoVersion(): void {
    $this->writePackageJson('client/svelte-kit/package.json', ['devDependencies' => ['svelte' => '^5.1.0']]);

    $extractor = new ProjectMetadataExtractor($this->kernel('test'), $this->configurationProvider(), 'dev');

    self::assertSame('dev', $extractor->version());
  }

  #[Test]
  #[Group('sc-900')]
  public function itReportsClientFrameworkVersionsFromPackageJsonWithoutVersion(): void {
    $this->writePackageJson('client/svelte-kit/package.json', [
      'devDependencies' => ['svelte' => '^5.1.0', '@sveltejs/kit' => '^3.2.0'],
    ]);

    $extractor = new ProjectMetadataExtractor($this->kernel('test'), $this->configurationProvider(), 'dev');

    $details = $this->detailsByLabel($extractor);

    self::assertSame('5.1.0', $details['Svelte']);
    self::assertSame('3.2.0', $details['SvelteKit']);
  }

  #[Test]
  #[Group('sc-900')]
  public function itReadsClientPackageJsonInDevEnvironment(): void {
    $this->writePackageJson('client/package.json', [
      'devDependencies' => ['svelte' => '^5.4.0', '@sveltejs/kit' => '^3.0.1'],
    ]);

    $extractor = new ProjectMetadataExtractor($this->kernel('dev'), $this->configurationProvider(), '1.15.0');

    $details = $this->detailsByLabel($extractor);

    self::assertSame('1.15.0', $extractor->version());
    self::assertSame('5.4.0', $details['Svelte']);
    self::assertSame('3.0.1', $details['SvelteKit']);
  }

  /**
   * @param array<string, mixed> $content
   */
  private function writePackageJson(string $relativePath, array $content): void {
    $path = $this->_scratch . '/' . $relativePath;
    \mkdir(\dirname($path), 0777, true);
    \file_put_contents($path, \json_encode($content, \JSON_THROW_ON_ERROR));
  }

  private function kernel(string $environment): KernelInterface {
    $kernel = $this->createStub(KernelInterface::class);
    $kernel->method('getEnvironment')->willReturn($environment);
    $kernel->method('getProjectDir')->willReturn($this->_scratch . '/api');

    return $kernel;
  }

  private function configurationProvider(): ConfigurationProviderInterface {
    return $this->createStub(ConfigurationProviderInterface::class);
  }

  /**
   * @return array<string, string>
   */
  private function detailsByLabel(ProjectMetadataExtractor $extractor): array {
    $details = [];

    foreach ($extractor->getClientAppDetails() as [$label, $value]) {
      $details[$label] = $value;
    }

    return $details;
  }
}
