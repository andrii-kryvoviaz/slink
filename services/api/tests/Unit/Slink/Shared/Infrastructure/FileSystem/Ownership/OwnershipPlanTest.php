<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Shared\Infrastructure\FileSystem\Ownership;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Infrastructure\FileSystem\Ownership\OwnershipEntry;
use Slink\Shared\Infrastructure\FileSystem\Ownership\OwnershipPlan;

final class OwnershipPlanTest extends TestCase {
  #[Test]
  public function itMirrorsTheCurrentShellScriptEntryListInOrder(): void {
    $plan = OwnershipPlan::fromStoragePaths('/app', '/services/api/var', '/data', '/run');

    $actual = \array_map($this->describe(...), $plan->getEntries());

    $expected = [
      ['path' => '/app', 'owner' => 'slink', 'group' => 'slink', 'mode' => null, 'recursive' => true, 'optional' => false, 'glob' => false],
      ['path' => '/app/var/data', 'owner' => 'www-data', 'group' => 'slink', 'mode' => null, 'recursive' => false, 'optional' => false, 'glob' => false],
      ['path' => '/services/api/var', 'owner' => 'www-data', 'group' => 'slink', 'mode' => null, 'recursive' => true, 'optional' => false, 'glob' => false],
      ['path' => '/app/var/data/*.db', 'owner' => 'www-data', 'group' => 'www-data', 'mode' => null, 'recursive' => false, 'optional' => true, 'glob' => true],
      ['path' => '/data/caddy', 'owner' => 'www-data', 'group' => 'slink', 'mode' => null, 'recursive' => true, 'optional' => false, 'glob' => false],
      ['path' => '/data/redis', 'owner' => 'redis', 'group' => 'slink', 'mode' => null, 'recursive' => true, 'optional' => false, 'glob' => false],
      ['path' => '/app/var/data', 'owner' => null, 'group' => null, 'mode' => 0o770, 'recursive' => false, 'optional' => false, 'glob' => false],
      ['path' => '/app/slink/images', 'owner' => null, 'group' => null, 'mode' => 0o770, 'recursive' => false, 'optional' => false, 'glob' => false],
      ['path' => '/app/slink/cache', 'owner' => null, 'group' => null, 'mode' => 0o770, 'recursive' => false, 'optional' => true, 'glob' => false],
      ['path' => '/app/var/data/keys', 'owner' => null, 'group' => null, 'mode' => 0o750, 'recursive' => false, 'optional' => false, 'glob' => false],
      ['path' => '/app/var/data/keys/private.pem', 'owner' => null, 'group' => null, 'mode' => 0o640, 'recursive' => false, 'optional' => true, 'glob' => false],
      ['path' => '/app/var/data/keys/passphrase', 'owner' => null, 'group' => null, 'mode' => 0o640, 'recursive' => false, 'optional' => true, 'glob' => false],
      ['path' => '/run', 'owner' => 'root', 'group' => 'root', 'mode' => null, 'recursive' => false, 'optional' => false, 'glob' => false],
    ];

    self::assertCount(13, $plan->getEntries());
    self::assertSame($expected, $actual);
  }

  #[Test]
  public function theRecursiveAppEntryIsWhatGivesImagesItsSlinkOwnership(): void {
    $plan = OwnershipPlan::fromStoragePaths('/app', '/services/api/var', '/data', '/run');

    $first = $plan->getEntries()[0];

    self::assertSame('/app', $first->getPath());
    self::assertSame('slink', $first->getOwner());
    self::assertSame('slink', $first->getGroup());
    self::assertTrue($first->isRecursive());
  }

  #[Test]
  public function imagesAndCacheRemainSlinkOwnedAndNotSetgid(): void {
    $plan = OwnershipPlan::fromStoragePaths('/app', '/services/api/var', '/data', '/run');

    foreach ($plan->getEntries() as $entry) {
      $isLeaf = \str_ends_with($entry->getPath(), '/slink/images') || \str_ends_with($entry->getPath(), '/slink/cache');

      if (!$isLeaf) {
        continue;
      }

      self::assertNotSame('www-data', $entry->getOwner());
      self::assertSame(0o770, $entry->getMode());
      self::assertNotSame(0o2770, $entry->getMode());
      self::assertFalse($entry->isRecursive());
    }
  }

  /**
   * @return array<string, mixed>
   */
  private function describe(OwnershipEntry $entry): array {
    return [
      'path' => $entry->getPath(),
      'owner' => $entry->getOwner(),
      'group' => $entry->getGroup(),
      'mode' => $entry->getMode(),
      'recursive' => $entry->isRecursive(),
      'optional' => $entry->isOptional(),
      'glob' => $entry->isGlob(),
    ];
  }
}
