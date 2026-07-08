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
      ['path' => '/app', 'owner' => 'slink', 'group' => 'slink', 'mode' => null, 'fileMode' => null, 'recursive' => true, 'optional' => false, 'glob' => false],
      ['path' => '/app/var/data', 'owner' => 'www-data', 'group' => 'slink', 'mode' => null, 'fileMode' => null, 'recursive' => false, 'optional' => false, 'glob' => false],
      ['path' => '/services/api/var', 'owner' => 'www-data', 'group' => 'slink', 'mode' => null, 'fileMode' => null, 'recursive' => true, 'optional' => false, 'glob' => false],
      ['path' => '/app/var/data/*.db*', 'owner' => 'www-data', 'group' => 'www-data', 'mode' => 0o660, 'fileMode' => null, 'recursive' => false, 'optional' => true, 'glob' => true],
      ['path' => '/data', 'owner' => 'slink', 'group' => 'slink', 'mode' => null, 'fileMode' => null, 'recursive' => false, 'optional' => false, 'glob' => false],
      ['path' => '/data/caddy', 'owner' => 'www-data', 'group' => 'slink', 'mode' => null, 'fileMode' => null, 'recursive' => true, 'optional' => false, 'glob' => false],
      ['path' => '/data/redis', 'owner' => 'redis', 'group' => 'slink', 'mode' => null, 'fileMode' => null, 'recursive' => true, 'optional' => false, 'glob' => false],
      ['path' => '/app/slink/images', 'owner' => 'www-data', 'group' => 'slink', 'mode' => null, 'fileMode' => null, 'recursive' => true, 'optional' => false, 'glob' => false],
      ['path' => '/app/slink/cache', 'owner' => 'www-data', 'group' => 'slink', 'mode' => null, 'fileMode' => null, 'recursive' => true, 'optional' => true, 'glob' => false],
      ['path' => '/app/slink/chunks', 'owner' => 'www-data', 'group' => 'slink', 'mode' => null, 'fileMode' => null, 'recursive' => true, 'optional' => true, 'glob' => false],
      ['path' => '/data', 'owner' => null, 'group' => null, 'mode' => 0o2771, 'fileMode' => null, 'recursive' => false, 'optional' => false, 'glob' => false],
      ['path' => '/data/caddy', 'owner' => null, 'group' => null, 'mode' => 0o2770, 'fileMode' => 0o660, 'recursive' => true, 'optional' => false, 'glob' => false],
      ['path' => '/data/redis', 'owner' => null, 'group' => null, 'mode' => 0o2770, 'fileMode' => 0o660, 'recursive' => true, 'optional' => false, 'glob' => false],
      ['path' => '/app/var/data', 'owner' => null, 'group' => null, 'mode' => 0o770, 'fileMode' => null, 'recursive' => false, 'optional' => false, 'glob' => false],
      ['path' => '/app/slink/images', 'owner' => null, 'group' => null, 'mode' => 0o2770, 'fileMode' => 0o660, 'recursive' => true, 'optional' => false, 'glob' => false],
      ['path' => '/app/slink/cache', 'owner' => null, 'group' => null, 'mode' => 0o2770, 'fileMode' => 0o660, 'recursive' => true, 'optional' => true, 'glob' => false],
      ['path' => '/app/slink/chunks', 'owner' => null, 'group' => null, 'mode' => 0o2770, 'fileMode' => 0o660, 'recursive' => true, 'optional' => true, 'glob' => false],
      ['path' => '/services/api/var/cache/prod', 'owner' => null, 'group' => null, 'mode' => 0o2770, 'fileMode' => null, 'recursive' => false, 'optional' => true, 'glob' => false],
      ['path' => '/app/var/data/keys', 'owner' => null, 'group' => null, 'mode' => 0o750, 'fileMode' => null, 'recursive' => false, 'optional' => false, 'glob' => false],
      ['path' => '/app/var/data/keys/private.pem', 'owner' => null, 'group' => null, 'mode' => 0o640, 'fileMode' => null, 'recursive' => false, 'optional' => true, 'glob' => false],
      ['path' => '/app/var/data/keys/passphrase', 'owner' => null, 'group' => null, 'mode' => 0o640, 'fileMode' => null, 'recursive' => false, 'optional' => true, 'glob' => false],
      ['path' => '/run', 'owner' => 'root', 'group' => 'root', 'mode' => null, 'fileMode' => null, 'recursive' => false, 'optional' => false, 'glob' => false],
    ];

    self::assertCount(22, $plan->getEntries());
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
  public function dataRootIsSlinkOwnedWithTraverseOnlyAccessForNonMembers(): void {
    $plan = OwnershipPlan::fromStoragePaths('/app', '/services/api/var', '/data', '/run');

    $ownership = $this->ownershipEntryFor($plan, '/data');
    $mode = $this->modeEntryFor($plan, '/data');

    self::assertSame('slink', $ownership->getOwner());
    self::assertSame('slink', $ownership->getGroup());
    self::assertFalse($ownership->isRecursive());
    self::assertSame(0o2771, $mode->getMode());
    self::assertFalse($mode->isRecursive());
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
      self::assertSame(0o660, $mode->getFileMode());
      self::assertTrue($mode->isRecursive());
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
      'fileMode' => $entry->getFileMode(),
      'recursive' => $entry->isRecursive(),
      'optional' => $entry->isOptional(),
      'glob' => $entry->isGlob(),
    ];
  }
}
