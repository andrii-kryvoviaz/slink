<?php

declare(strict_types=1);

namespace Tests\Integration\Slink\Shared\Application\Service;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Slink\Shared\Application\Service\ProjectMetadataExtractor;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

final class ProjectMetadataExtractorVersionTest extends KernelTestCase {
  private const string VARIABLE = 'SLINK_VERSION';

  private string $_scratch;

  /**
   * @var array{env: mixed, server: mixed, getenv: string|false}
   */
  private array $_previous;

  protected function setUp(): void {
    $this->_previous = [
      'env' => $_ENV[self::VARIABLE] ?? null,
      'server' => $_SERVER[self::VARIABLE] ?? null,
      'getenv' => \getenv(self::VARIABLE),
    ];

    $this->_scratch = \sys_get_temp_dir() . '/slink_project_metadata_' . \bin2hex(\random_bytes(6));
    \mkdir($this->_scratch . '/api', 0777, true);
    \mkdir($this->_scratch . '/client/svelte-kit', 0777, true);
    \file_put_contents(
      $this->_scratch . '/client/svelte-kit/package.json',
      \json_encode(['devDependencies' => ['svelte' => '^5.1.0']], \JSON_THROW_ON_ERROR),
    );
  }

  protected function tearDown(): void {
    parent::tearDown();

    $this->restoreVariable();

    @\unlink($this->_scratch . '/client/svelte-kit/package.json');

    foreach (['client/svelte-kit', 'client', 'api', ''] as $dir) {
      @\rmdir($this->_scratch . '/' . $dir);
    }
  }

  #[Test]
  #[Group('sc-900')]
  public function itResolvesVersionToDevWhenSlinkVersionIsUnset(): void {
    $this->unsetVariable();

    self::assertFalse(\getenv(self::VARIABLE));
    self::assertSame('dev', $this->resolveExtractor()->version());
  }

  #[Test]
  #[Group('sc-900')]
  public function itResolvesVersionFromSlinkVersionEnvironment(): void {
    $this->setVariable('1.15.0');

    self::assertSame('1.15.0', $this->resolveExtractor()->version());
  }

  private function resolveExtractor(): ProjectMetadataExtractor {
    $kernel = $this->createStub(KernelInterface::class);
    $kernel->method('getEnvironment')->willReturn('test');
    $kernel->method('getProjectDir')->willReturn($this->_scratch . '/api');

    $container = static::getContainer();
    $container->set('kernel', $kernel);

    $extractor = $container->get(ProjectMetadataExtractor::class);
    self::assertInstanceOf(ProjectMetadataExtractor::class, $extractor);

    return $extractor;
  }

  private function unsetVariable(): void {
    unset($_ENV[self::VARIABLE], $_SERVER[self::VARIABLE]);
    \putenv(self::VARIABLE);
  }

  private function setVariable(string $value): void {
    $_ENV[self::VARIABLE] = $value;
    $_SERVER[self::VARIABLE] = $value;
    \putenv(self::VARIABLE . '=' . $value);
  }

  private function restoreVariable(): void {
    $this->unsetVariable();

    if ($this->_previous['env'] !== null) {
      $_ENV[self::VARIABLE] = $this->_previous['env'];
    }

    if ($this->_previous['server'] !== null) {
      $_SERVER[self::VARIABLE] = $this->_previous['server'];
    }

    if ($this->_previous['getenv'] !== false) {
      \putenv(self::VARIABLE . '=' . $this->_previous['getenv']);
    }
  }
}
