<?php

declare(strict_types=1);

namespace Tests\Integration\Permissions;

final class PermissionScenario {
  private const int NASGRP_GID = 5000;
  private const int WWW_DATA_UID = 33;
  private const int SLINK_GID = 1000;

  private readonly string $_app;
  private readonly string $_images;
  private readonly string $_cache;

  private function __construct(
    private readonly string $_testRoot,
  ) {
    $this->_app = $this->_testRoot . '/app';
    $this->_images = $this->_app . '/slink/images';
    $this->_cache = $this->_app . '/slink/cache';
  }

  public static function create(): self {
    $base = '/opt/slink_tests';
    @mkdir($base, 0o755, true);

    $testRoot = $base . '/perm_' . bin2hex(random_bytes(6));
    if (!@mkdir($testRoot, 0o755, true)) {
      throw new \RuntimeException('unable to create test root at ' . $testRoot);
    }

    return new self($testRoot);
  }

  public function testRoot(): string {
    return $this->_testRoot;
  }

  public function imagesDir(): string {
    return $this->_images;
  }

  public function cacheDir(): string {
    return $this->_cache;
  }

  public function cleanup(): void {
    PermissionProcess::run(['rm', '-rf', $this->_testRoot]);
  }

  public function setupNasGroup(): void {
    $group = PermissionProcess::shell('grep -q "^nasgrp:" /etc/group');
    if ($group->isSuccessful()) {
      return;
    }

    PermissionProcess::run(['addgroup', '-g', (string) self::NASGRP_GID, 'nasgrp']);
  }

  public function buildImageState(): void {
    $directories = [
      $this->_images,
      $this->_cache,
      $this->_app . '/var/data/keys',
      $this->_testRoot . '/services/api/var',
      $this->_testRoot . '/data/caddy',
      $this->_testRoot . '/data/redis',
    ];

    foreach ($directories as $directory) {
      @mkdir($directory, 0o770, true);
    }

    file_put_contents($this->_images . '/old.jpg', "FAKE IMAGE DATA\n");
    file_put_contents($this->_cache . '/old_variant.jpg', "FAKE CACHE DATA\n");

    PermissionProcess::run(['chmod', '755', $this->_testRoot]);

    PermissionProcess::run(['chown', '-R', '1000:1000', $this->_app]);
    PermissionProcess::run(['chmod', '-R', '770', $this->_app]);
    PermissionProcess::run(['chown', '-R', '1000:1000', $this->_testRoot . '/services/api/var']);
    PermissionProcess::run(['chown', '-R', '1000:1000', $this->_testRoot . '/data/caddy']);
    PermissionProcess::run(['chown', '-R', '1000:1000', $this->_testRoot . '/data/redis']);
  }

  public function applyNasLeafModel(): void {
    PermissionProcess::run(['chgrp', (string) self::NASGRP_GID, $this->_images]);
    PermissionProcess::run(['chgrp', (string) self::NASGRP_GID, $this->_cache]);
    PermissionProcess::run(['chgrp', (string) self::NASGRP_GID, $this->_images . '/old.jpg']);
    PermissionProcess::run(['chgrp', (string) self::NASGRP_GID, $this->_cache . '/old_variant.jpg']);
  }

  public function applyLeafOwnerFix(): void {
    PermissionProcess::run(['chown', '-R', 'www-data:slink', $this->_images]);
    PermissionProcess::run(['chown', '-R', 'www-data:slink', $this->_cache]);
  }

  public function runOwnershipFix(): PermissionProcess {
    @mkdir($this->_testRoot . '/run', 0o755, true);

    return PermissionProcess::run([
      'env',
      'SLINK_APP_DIR=' . $this->_app,
      'SLINK_API_VAR_DIR=' . $this->_testRoot . '/services/api/var',
      'SLINK_DATA_DIR=' . $this->_testRoot . '/data',
      'SLINK_RUN_DIR=' . $this->_testRoot . '/run',
      'slink',
      'slink:storage:fix-ownership',
    ]);
  }

  public function asWww(string $command): PermissionProcess {
    return PermissionProcess::run([
      'setpriv',
      '--reuid', (string) self::WWW_DATA_UID,
      '--regid', (string) self::WWW_DATA_UID,
      '--groups', self::WWW_DATA_UID . ',' . self::SLINK_GID,
      '--',
      'sh', '-c', $command,
    ]);
  }

  public function asWwwNoGroup(string $command): PermissionProcess {
    return PermissionProcess::run([
      'setpriv',
      '--reuid', (string) self::WWW_DATA_UID,
      '--regid', (string) self::WWW_DATA_UID,
      '--clear-groups',
      '--',
      'sh', '-c', $command,
    ]);
  }

  public function probeUpload(callable $runner): bool {
    $target = $this->_images . '/new_upload_' . getmypid() . '.jpg';

    return $runner("touch '{$target}'")->isSuccessful();
  }

  public function probeReadExisting(callable $runner): bool {
    $target = $this->_images . '/old.jpg';

    return $runner("cat '{$target}' > /dev/null")->isSuccessful();
  }

  public function probeDelete(callable $runner): bool {
    $target = $this->_images . '/old.jpg';

    return $runner("rm -f '{$target}'")->isSuccessful();
  }

  public function probeCacheWrite(callable $runner): bool {
    $target = $this->_cache . '/new_cache_' . getmypid() . '.jpg';

    return $runner("touch '{$target}'")->isSuccessful();
  }

  public function probeGroupInherit(callable $runner): bool {
    $target = $this->_images . '/inherit_test_' . getmypid() . '.jpg';

    $created = $runner("touch '{$target}'");
    if (!$created->isSuccessful()) {
      return false;
    }

    return $this->fileGid($target) === self::SLINK_GID;
  }

  public function fileOwnerUid(string $path): ?int {
    $result = PermissionProcess::run(['stat', '-c', '%u', $path]);

    if (!$result->isSuccessful()) {
      return null;
    }

    return (int) $result->stdout();
  }

  private function fileGid(string $path): ?int {
    $result = PermissionProcess::run(['stat', '-c', '%g', $path]);

    if (!$result->isSuccessful()) {
      return null;
    }

    return (int) $result->stdout();
  }

  public function phpProbeOpenBasedir(?string $baseDir = null): string {
    $baseDir ??= $this->_app;
    $code = '$f=@fopen("/tmp/x","r"); echo $f===false?"DENIED":"OK";';

    return PermissionProcess::run([
      'php',
      '-d', 'open_basedir=' . $baseDir,
      '-r', $code,
    ])->stdout();
  }

  public function phpProbeOpenBasedirUpload(?string $baseDir = null): string {
    $baseDir ??= $this->_app;
    $code = 'error_reporting(E_ALL); $f=@fopen("/tmp/upload_test.bin","w");'
      . ' if($f===false){echo "BLOCKED";}else{fclose($f);echo "ALLOWED";}';

    return PermissionProcess::run([
      'php',
      '-d', 'open_basedir=' . $baseDir,
      '-r', $code,
    ])->stdout();
  }

  public static function isRoot(): bool {
    return PermissionProcess::shell('test "$(id -u)" = "0"')->isSuccessful();
  }

  public static function hasCommand(string $command): bool {
    return PermissionProcess::shell('command -v ' . escapeshellarg($command) . ' >/dev/null 2>&1')->isSuccessful();
  }
}
