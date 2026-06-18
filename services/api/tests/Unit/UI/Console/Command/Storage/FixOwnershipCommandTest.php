<?php

declare(strict_types=1);

namespace Tests\Unit\UI\Console\Command\Storage;

use FilesystemIterator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Slink\Shared\Infrastructure\FileSystem\Ownership\OwnershipApplier;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use UI\Console\Command\Storage\FixOwnershipCommand;

final class FixOwnershipCommandTest extends TestCase {
  private string $_scratch;

  protected function setUp(): void {
    $this->_scratch = \sys_get_temp_dir() . '/slink_ownership_cmd_' . \bin2hex(\random_bytes(6));
    $this->buildTree();
  }

  protected function tearDown(): void {
    $this->removeTree($this->_scratch);

    \putenv('SLINK_APP_DIR');
    \putenv('SLINK_API_VAR_DIR');
    \putenv('SLINK_DATA_DIR');
    \putenv('SLINK_RUN_DIR');
  }

  #[Test]
  public function itAppliesOwnershipFromExplicitOptions(): void {
    $tester = new CommandTester(new FixOwnershipCommand(new OwnershipApplier()));

    $exitCode = $tester->execute([
      '--app-dir' => $this->appDir(),
      '--api-var-dir' => $this->_scratch . '/services/api/var',
      '--data-dir' => $this->_scratch . '/data',
      '--run-dir' => $this->_scratch . '/run',
    ]);

    self::assertSame(Command::SUCCESS, $exitCode);
    self::assertSame(0o2770, $this->permsOf($this->appDir() . '/slink/images'));
    self::assertSame(0o750, $this->permsOf($this->appDir() . '/var/data/keys'));
  }

  #[Test]
  public function itFallsBackToEnvironmentDefaultsWhenOptionsAreOmitted(): void {
    \putenv('SLINK_APP_DIR=' . $this->appDir());
    \putenv('SLINK_API_VAR_DIR=' . $this->_scratch . '/services/api/var');
    \putenv('SLINK_DATA_DIR=' . $this->_scratch . '/data');
    \putenv('SLINK_RUN_DIR=' . $this->_scratch . '/run');

    $tester = new CommandTester(new FixOwnershipCommand(new OwnershipApplier()));

    $exitCode = $tester->execute([]);

    self::assertSame(Command::SUCCESS, $exitCode);
    self::assertSame(0o2770, $this->permsOf($this->appDir() . '/slink/images'));
  }

  private function appDir(): string {
    return $this->_scratch . '/app';
  }

  private function buildTree(): void {
    $directories = [
      $this->appDir() . '/var/data/keys',
      $this->appDir() . '/slink/images',
      $this->appDir() . '/slink/cache',
      $this->_scratch . '/services/api/var',
      $this->_scratch . '/data/caddy',
      $this->_scratch . '/data/redis',
      $this->_scratch . '/run',
    ];

    foreach ($directories as $directory) {
      \mkdir($directory, 0o777, true);
    }
  }

  private function permsOf(string $path): int {
    \clearstatcache(true, $path);

    return \fileperms($path) & 0o7777;
  }

  private function removeTree(string $path): void {
    if (!\is_dir($path)) {
      return;
    }

    $iterator = new RecursiveIteratorIterator(
      new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
      RecursiveIteratorIterator::CHILD_FIRST,
    );

    foreach ($iterator as $item) {
      $this->removeNode($item->getPathname(), $item->isDir());
    }

    \rmdir($path);
  }

  private function removeNode(string $path, bool $isDir): void {
    if ($isDir) {
      \rmdir($path);

      return;
    }

    \unlink($path);
  }
}
