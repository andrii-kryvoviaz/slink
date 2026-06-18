<?php

declare(strict_types=1);

namespace Tests\Integration\Permissions;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Ticket;
use PHPUnit\Framework\Attributes\TestDox;

#[Group('permissions')]
#[Ticket('209')]
final class RuntimeConstraintsTest extends PermissionTestCase {
  #[Test]
  #[TestDox('Without CAP_CHOWN, a chown attempt fails with EPERM and leaves the already-correct www-data ownership intact')]
  public function droppedChownCapabilityPreservesExistingOwnership(): void {
    $scenario = $this->bootBareScenario();
    $scenario->buildImageState();
    $scenario->runOwnershipFix();
    $scenario->applyLeafOwnerFix();

    self::assertSame(
      self::WWW_DATA_UID,
      $scenario->fileOwnerUid($scenario->imagesDir()),
      'pre-correct state must be www-data (uid 33) before the cap-drop simulation',
    );

    $chown = $scenario->asWww("chown root '" . $scenario->imagesDir() . "'");
    self::assertFalse($chown->isSuccessful(), 'chown without CAP_CHOWN must fail with EPERM');

    self::assertSame(
      self::WWW_DATA_UID,
      $scenario->fileOwnerUid($scenario->imagesDir()),
      'ownership must be unchanged from the pre-correct state (www-data uid 33)',
    );
  }

  #[Test]
  #[TestDox('On a read-only mount, chmod and chown are rejected with EROFS or EPERM')]
  public function readOnlyRootfsRejectsOwnershipChange(): void {
    $this->requireRoot('mount tmpfs');
    $scenario = $this->bootBareScenario();

    $roDir = $scenario->testRoot() . '/mnt/ro_overlay';
    @mkdir($roDir, 0o755, true);

    $mount = PermissionProcess::shell("mount -t tmpfs -o ro,size=1m tmpfs '{$roDir}'");
    if (!$mount->isSuccessful()) {
      self::markTestSkipped('cannot mount read-only tmpfs (kernel/privilege constraint)');
    }

    $chmod = PermissionProcess::run(['chmod', '770', $roDir]);
    PermissionProcess::shell("umount -l '{$roDir}' 2>/dev/null || true");

    self::assertFalse($chmod->isSuccessful(), 'chmod on a read-only mount must fail (EROFS/EPERM)');
  }

  #[Test]
  #[TestDox('With open_basedir excluding /tmp, PHP is denied access to /tmp')]
  public function openBasedirBlocksUploadTmpRead(): void {
    $this->requireCommand('php', 'php-cli not available');
    $scenario = $this->bootBareScenario();

    self::assertSame('DENIED', $scenario->phpProbeOpenBasedir(), 'open_basedir must block /tmp access at the PHP level');
  }

  #[Test]
  #[TestDox('With open_basedir scoped to the app dir, PHP cannot reach the upload temp path under /tmp')]
  public function openBasedirBlocksUploadTempPath(): void {
    $this->requireCommand('php', 'php-cli not available');
    $scenario = $this->bootBareScenario();

    self::assertSame('BLOCKED', $scenario->phpProbeOpenBasedirUpload(), 'upload temp path under /tmp must be blocked');
  }

  #[Test]
  #[TestDox('The ownership fix changes a symlink itself, not its target, so a planted symlink cannot redirect the recursive chown to a file outside the storage tree')]
  public function ownershipFixDoesNotFollowSymlinkOutsideStorageTree(): void {
    $scenario = $this->bootBareScenario();
    $scenario->buildImageState();

    $outside = $scenario->testRoot() . '/outside_target';
    file_put_contents($outside, "outside\n");
    self::assertSame(0, $scenario->fileOwnerUid($outside), 'the outside file must start root-owned');

    symlink($outside, $scenario->imagesDir() . '/evil_link');

    $scenario->runOwnershipFix();

    self::assertSame(
      0,
      $scenario->fileOwnerUid($outside),
      'the ownership fix must not follow a symlink to chown a file outside the storage tree',
    );
  }

  #[Test]
  #[TestDox('The recursive chown does not follow a symlinked directory, so a root-owned file inside the link target keeps its ownership')]
  public function ownershipFixDoesNotDescendIntoSymlinkedDirectory(): void {
    $scenario = $this->bootBareScenario();
    $scenario->buildImageState();

    $outsideDir = $scenario->testRoot() . '/outside_dir';
    @mkdir($outsideDir, 0o755, true);

    $outsideFile = $outsideDir . '/secret.jpg';
    file_put_contents($outsideFile, "outside\n");
    self::assertSame(0, $scenario->fileOwnerUid($outsideFile), 'the file inside the outside dir must start root-owned');

    symlink($outsideDir, $scenario->imagesDir() . '/evil_dir');

    $scenario->runOwnershipFix();

    self::assertSame(
      0,
      $scenario->fileOwnerUid($outsideFile),
      'the ownership fix must not descend into a symlinked directory to chown files inside it',
    );
  }
}
