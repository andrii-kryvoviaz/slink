<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Shared\Infrastructure\FileSystem\Ownership;

use FilesystemIterator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Slink\Shared\Infrastructure\FileSystem\Ownership\OwnershipApplier;
use Slink\Shared\Infrastructure\FileSystem\Ownership\OwnershipPlan;

final class OwnershipApplierTest extends TestCase {
  private string $_scratch;

  protected function setUp(): void {
    $this->_scratch = \sys_get_temp_dir() . '/slink_ownership_' . \bin2hex(\random_bytes(6));
    $this->buildTree();
  }

  protected function tearDown(): void {
    $this->removeTree($this->_scratch);
  }

  #[Test]
  public function itAppliesChmodModesToDirectories(): void {
    (new OwnershipApplier())->apply($this->plan());

    self::assertSame(0o770, $this->permsOf($this->appDir() . '/slink/images'));
    self::assertSame(0o770, $this->permsOf($this->appDir() . '/var/data'));
    self::assertSame(0o750, $this->permsOf($this->appDir() . '/var/data/keys'));
  }

  #[Test]
  public function itWalksRecursiveTargetsWithoutThrowingAndKeepsTheTreeIntact(): void {
    $deep = $this->appDir() . '/sub/deep';
    \mkdir($deep, 0o770, true);
    \file_put_contents($deep . '/file.txt', "data\n");

    (new OwnershipApplier())->apply($this->plan());

    self::assertFileExists($deep . '/file.txt');
    self::assertDirectoryExists($deep);
  }

  #[Test]
  public function itExpandsGlobTargetsWithoutErroring(): void {
    $data = $this->appDir() . '/var/data';
    \file_put_contents($data . '/a.db', '');
    \file_put_contents($data . '/b.db', '');
    \file_put_contents($data . '/notes.txt', '');

    (new OwnershipApplier())->apply($this->plan());

    self::assertFileExists($data . '/a.db');
    self::assertFileExists($data . '/b.db');
    self::assertFileExists($data . '/notes.txt');
  }

  #[Test]
  public function itToleratesOptionalMissingPaths(): void {
    $missing = $this->appDir() . '/var/data/keys/private.pem';

    (new OwnershipApplier())->apply($this->plan());

    self::assertFileDoesNotExist($missing);
  }

  #[Test]
  public function itAppliesModeToTheTargetDirectoryOnlyNotRecursively(): void {
    $keys = $this->appDir() . '/var/data/keys';
    $child = $keys . '/child';
    \mkdir($child, 0o700, true);
    \chmod($keys, 0o700);
    \chmod($child, 0o700);

    (new OwnershipApplier())->apply($this->plan());

    self::assertSame(0o750, $this->permsOf($keys));
    self::assertSame(0o700, $this->permsOf($child));
  }

  #[Test]
  public function itAppliesTheWholeTreeAndReturnsVoid(): void {
    (new OwnershipApplier())->apply($this->plan());

    $this->expectNotToPerformAssertions();
  }

  private function plan(): OwnershipPlan {
    return OwnershipPlan::fromStoragePaths(
      $this->appDir(),
      $this->_scratch . '/services/api/var',
      $this->_scratch . '/data',
      $this->_scratch . '/run',
    );
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
