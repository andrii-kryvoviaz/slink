<?php

declare(strict_types=1);

namespace Tests\Unit\UI\Console\Command\Image;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Image\Infrastructure\ChunkedUpload\Storage\ChunkStorageInterface;
use Symfony\Component\Console\Tester\CommandTester;
use UI\Console\Command\Image\ChunkedUploadGarbageCollectCommand;

final class ChunkedUploadGarbageCollectCommandTest extends TestCase {
  #[Test]
  public function itDeletesUploadsOlderThanTheCutoffAndKeepsRecentOnes(): void {
    $now = \time();

    $storage = $this->createStub(ChunkStorageInterface::class);
    $storage->method('listUploadIds')->willReturn(['old', 'fresh', 'missing']);
    $storage->method('lastModified')->willReturnMap([
      ['old', $now - 100000],
      ['fresh', $now],
      ['missing', null],
    ]);

    $deleted = [];
    $storage->method('deleteUpload')->willReturnCallback(static function (string $uploadId) use (&$deleted): void {
      $deleted[] = $uploadId;
    });

    $tester = $this->tester($storage);
    $exitCode = $tester->execute([]);

    self::assertSame(0, $exitCode);
    self::assertSame(['old'], $deleted);
  }

  #[Test]
  public function itRespectsTheGraceOption(): void {
    $now = \time();

    $storage = $this->createMock(ChunkStorageInterface::class);
    $storage->method('listUploadIds')->willReturn(['borderline']);
    $storage->method('lastModified')->willReturn($now - 5000);

    $storage->expects(self::never())->method('deleteUpload');

    $tester = $this->tester($storage);
    $exitCode = $tester->execute(['--grace' => '1000000']);

    self::assertSame(0, $exitCode);
  }

  private function tester(ChunkStorageInterface $storage): CommandTester {
    return new CommandTester(new ChunkedUploadGarbageCollectCommand($storage));
  }
}
