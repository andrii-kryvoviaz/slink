<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Shared\Infrastructure\FileSystem\Ownership;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Infrastructure\FileSystem\Ownership\OwnershipEntry;
use Slink\Shared\Infrastructure\FileSystem\Ownership\OwnershipPlan;

final class OwnershipPlanTest extends TestCase {
  #[Test]
  public function itMatchesTheDesiredStorageOwnershipPlanInOrder(): void {
    $plan = OwnershipPlan::fromStoragePaths('/app', '/services/api/var', '/data', '/run');

    $actual = \array_map($this->describe(...), $plan->getEntries());

    $expected = [
      ['path' => '/app', 'owner' => 'slink', 'group' => 'slink', 'mode' => null, 'recursive' => true, 'optional' => false, 'glob' => false],
      ['path' => '/app/var/data', 'owner' => 'www-data', 'group' => 'slink', 'mode' => null, 'recursive' => false, 'optional' => false, 'glob' => false],
      ['path' => '/services/api/var', 'owner' => 'www-data', 'group' => 'slink', 'mode' => null, 'recursive' => true, 'optional' => false, 'glob' => false],
      ['path' => '/app/var/data/*.db', 'owner' => 'www-data', 'group' => 'www-data', 'mode' => null, 'recursive' => false, 'optional' => true, 'glob' => true],
      ['path' => '/data/caddy', 'owner' => 'www-data', 'group' => 'slink', 'mode' => null, 'recursive' => true, 'optional' => false, 'glob' => false],
      ['path' => '/data/redis', 'owner' => 'redis', 'group' => 'slink', 'mode' => null, 'recursive' => true, 'optional' => false, 'glob' => false],
      ['path' => '/app/slink/images', 'owner' => 'www-data', 'group' => 'slink', 'mode' => null, 'recursive' => true, 'optional' => false, 'glob' => false],
      ['path' => '/app/slink/cache', 'owner' => 'www-data', 'group' => 'slink', 'mode' => null, 'recursive' => true, 'optional' => true, 'glob' => false],
      ['path' => '/app/slink/chunks', 'owner' => 'www-data', 'group' => 'slink', 'mode' => null, 'recursive' => true, 'optional' => true, 'glob' => false],
      ['path' => '/app/var/data', 'owner' => null, 'group' => null, 'mode' => 0o770, 'recursive' => false, 'optional' => false, 'glob' => false],
      ['path' => '/app/slink/images', 'owner' => null, 'group' => null, 'mode' => 0o2770, 'recursive' => false, 'optional' => false, 'glob' => false],
      ['path' => '/app/slink/cache', 'owner' => null, 'group' => null, 'mode' => 0o2770, 'recursive' => false, 'optional' => true, 'glob' => false],
      ['path' => '/app/slink/chunks', 'owner' => null, 'group' => null, 'mode' => 0o2770, 'recursive' => false, 'optional' => true, 'glob' => false],
      ['path' => '/services/api/var/cache/prod', 'owner' => null, 'group' => null, 'mode' => 0o2770, 'recursive' => false, 'optional' => true, 'glob' => false],
      ['path' => '/app/var/data/keys', 'owner' => null, 'group' => null, 'mode' => 0o750, 'recursive' => false, 'optional' => false, 'glob' => false],
      ['path' => '/app/var/data/keys/private.pem', 'owner' => null, 'group' => null, 'mode' => 0o640, 'recursive' => false, 'optional' => true, 'glob' => false],
      ['path' => '/app/var/data/keys/passphrase', 'owner' => null, 'group' => null, 'mode' => 0o640, 'recursive' => false, 'optional' => true, 'glob' => false],
      ['path' => '/run', 'owner' => 'root', 'group' => 'root', 'mode' => null, 'recursive' => false, 'optional' => false, 'glob' => false],
    ];

    self::assertCount(18, $plan->getEntries());
    self::assertSame($expected, $actual);
  }

  #[Test]
  public function prodCacheDirIsGroupWritableWithSetgidAndOptional(): void {
    $plan = OwnershipPlan::fromStoragePaths('/app', '/services/api/var', '/data', '/run');

    $mode = $this->modeEntryFor($plan, '/services/api/var/cache/prod');

    self::assertSame(0o2770, $mode->getMode());
    self::assertTrue($mode->isOptional());
    self::assertFalse($mode->isRecursive());
  }

  #[Test]
  public function theFirstEntryTakesRecursiveSlinkOwnershipOfTheWholeAppTree(): void {
    $plan = OwnershipPlan::fromStoragePaths('/app', '/services/api/var', '/data', '/run');

    $first = $plan->getEntries()[0];

    self::assertSame('/app', $first->getPath());
    self::assertSame('slink', $first->getOwner());
    self::assertSame('slink', $first->getGroup());
    self::assertTrue($first->isRecursive());
  }

  #[Test]
  public function imagesCacheAndChunksAreWwwDataOwnedRecursivelyWithSetgid(): void {
    $plan = OwnershipPlan::fromStoragePaths('/app', '/services/api/var', '/data', '/run');

    foreach (['/app/slink/images', '/app/slink/cache', '/app/slink/chunks'] as $leaf) {
      $ownership = $this->ownershipEntryFor($plan, $leaf);
      $mode = $this->modeEntryFor($plan, $leaf);

      self::assertSame('www-data', $ownership->getOwner());
      self::assertSame('slink', $ownership->getGroup());
      self::assertTrue($ownership->isRecursive());
      self::assertSame(0o2770, $mode->getMode());
    }
  }

  private function ownershipEntryFor(OwnershipPlan $plan, string $path): OwnershipEntry {
    foreach ($plan->getEntries() as $entry) {
      if ($entry->getPath() === $path && $entry->getOwner() !== null) {
        return $entry;
      }
    }

    self::fail(\sprintf('No ownership entry found for %s', $path));
  }

  private function modeEntryFor(OwnershipPlan $plan, string $path): OwnershipEntry {
    foreach ($plan->getEntries() as $entry) {
      if ($entry->getPath() === $path && $entry->getMode() !== null) {
        return $entry;
      }
    }

    self::fail(\sprintf('No mode entry found for %s', $path));
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
