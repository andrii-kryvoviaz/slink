<?php

declare(strict_types=1);

namespace Tests\Integration\Permissions;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Ticket;
use PHPUnit\Framework\Attributes\TestDox;

#[Group('permissions')]
#[Ticket('209')]
final class DirectoryOwnershipAccessTest extends PermissionTestCase {
  #[Test]
  #[TestDox('When the filesystem honors the slink supplementary group, www-data can upload, read, delete and write cache')]
  public function uploadSucceedsWhenSupplementaryGroupHonored(): void {
    $scenario = $this->bootDacScenario();
    $www = static fn (string $c): PermissionProcess => $scenario->asWww($c);

    $granted = $scenario->probeUpload($www)
      && $scenario->probeReadExisting($www)
      && $scenario->probeDelete($www)
      && $scenario->probeCacheWrite($www);

    self::assertTrue($granted, 'baseline group-honored access must be fully granted');
  }

  #[Test]
  #[Group('permissions-pending-fix')]
  #[TestDox('On a NAS leaf where the supplementary group is ineffective, www-data can upload, delete and write cache only after the entrypoint takes leaf ownership')]
  public function uploadRequiresOwnerWhenGroupIneffectiveOnLeaf(): void {
    $scenario = $this->bootDacScenario();
    $scenario->applyNasLeafModel();
    $www = static fn (string $c): PermissionProcess => $scenario->asWww($c);

    $granted = $scenario->probeUpload($www)
      && $scenario->probeDelete($www)
      && $scenario->probeCacheWrite($www);

    self::assertTrue($granted, 'DESIRED entrypoint owner fix not in place: leaf group grant is ineffective');
  }

  #[Test]
  #[Group('permissions-pending-fix')]
  #[TestDox('On a NAS leaf, a pre-existing file is readable by www-data only after the entrypoint applies a recursive ownership fix')]
  public function readingPreexistingFileRequiresRecursiveOwnerFix(): void {
    $scenario = $this->bootDacScenario();
    $scenario->applyNasLeafModel();

    $readable = $scenario->probeReadExisting(static fn (string $c): PermissionProcess => $scenario->asWww($c));

    self::assertTrue($readable, 'DESIRED recursive chown not in place: pre-existing file unreadable');
  }

  #[Test]
  #[Group('permissions-pending-fix')]
  #[TestDox('With an ACL mask of r-x on a NAS leaf, www-data can still upload because the entrypoint ownership fix bypasses the group mask')]
  public function ownerAccessBypassesAclMask(): void {
    if (!PermissionScenario::hasCommand('setfacl')) {
      self::markTestSkipped('setfacl not available (install acl); this test requires POSIX ACL support.');
    }

    $scenario = $this->bootDacScenario();
    $scenario->applyNasLeafModel();
    PermissionProcess::run(['setfacl', '-m', 'u::rwx', $scenario->imagesDir()]);
    PermissionProcess::run(['setfacl', '-m', 'g:slink:rwx', $scenario->imagesDir()]);
    PermissionProcess::run(['setfacl', '-m', 'm::r-x', $scenario->imagesDir()]);

    $granted = $scenario->probeUpload(static fn (string $c): PermissionProcess => $scenario->asWww($c));

    self::assertTrue($granted, 'DESIRED owner fix not in place: ACL mask r-x denies the group path');
  }

  #[Test]
  #[Group('permissions-pending-fix')]
  #[TestDox('On a bind mount without setgid, new uploads inherit the slink group once the entrypoint sets the setgid bit')]
  public function newUploadsInheritSlinkGroupViaSetgid(): void {
    $scenario = $this->bootDacScenario();

    $inherited = $scenario->probeGroupInherit(static fn (string $c): PermissionProcess => $scenario->asWww($c));

    self::assertTrue($inherited, 'DESIRED chmod g+s not in place: new files do not inherit the slink gid');
  }

  #[Test]
  #[TestDox('When the slink group is lost across the whole tree, a leaf-only ownership fix is insufficient because parent traversal still fails for a www-data process without the slink group')]
  public function leafOnlyOwnerFixInsufficientForWholeTreeGroupLoss(): void {
    $scenario = $this->bootDacScenario();
    $scenario->applyLeafOwnerFix();

    $granted = $scenario->probeUpload(static fn (string $c): PermissionProcess => $scenario->asWwwNoGroup($c));

    self::assertFalse($granted, 'leaf-only fix must not rescue whole-tree group loss');
  }
}
