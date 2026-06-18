<?php

declare(strict_types=1);

namespace Tests\Integration\Permissions;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('permissions')]
abstract class PermissionTestCase extends TestCase {
  protected const int WWW_DATA_UID = 33;
  protected const int SLINK_GID = 1000;

  private ?PermissionScenario $_scenario = null;

  #[\Override]
  protected function tearDown(): void {
    $this->_scenario?->cleanup();
    $this->_scenario = null;
  }

  protected function bootDacScenario(): PermissionScenario {
    $scenario = $this->bootBareScenario();
    $scenario->buildImageState();
    $scenario->runOwnershipFix();

    return $scenario;
  }

  protected function bootBareScenario(): PermissionScenario {
    $this->requireSandbox();

    $scenario = PermissionScenario::create();
    $scenario->setupNasGroup();
    $this->_scenario = $scenario;

    if (!PermissionScenario::isRoot()) {
      self::markTestSkipped('permission matrix must run as root (chown/setpriv/mount); run inside the permissions-test container');
    }

    return $scenario;
  }

  private function requireSandbox(): void {
    $marker = getenv('SLINK_PERMTEST_SANDBOX') === '1';
    $containerized = file_exists('/.dockerenv');
    if (!$marker || !$containerized) {
      throw new \RuntimeException(
        'refusing to run the destructive permission matrix outside its sandbox: '
        . 'run it only via docker compose -f docker/docker-compose.permissions-test.yaml '
        . '(expects SLINK_PERMTEST_SANDBOX=1 inside the slink:permissions-test container)'
      );
    }
  }

  protected function requireRoot(string $reason): void {
    if (!PermissionScenario::isRoot()) {
      self::markTestSkipped('must run as root to ' . $reason);
    }
  }

  protected function requireCommand(string $command, string $reason): void {
    if (!PermissionScenario::hasCommand($command)) {
      self::markTestSkipped($command . ' not available (' . $reason . ')');
    }
  }
}
