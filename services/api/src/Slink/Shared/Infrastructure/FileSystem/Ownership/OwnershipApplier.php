<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\FileSystem\Ownership;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class OwnershipApplier {
  public function apply(OwnershipPlan $plan): void {
    foreach ($plan->getEntries() as $entry) {
      $this->applyEntry($entry);
    }
  }

  private function applyEntry(OwnershipEntry $entry): void {
    foreach ($this->resolvePaths($entry) as $path) {
      if (!\file_exists($path) && !\is_link($path)) {
        continue;
      }

      $this->applyOwnership($entry, $path);
      $this->applyMode($entry, $path);
    }
  }

  /**
   * @return list<string>
   */
  private function resolvePaths(OwnershipEntry $entry): array {
    if (!$entry->isGlob()) {
      return [$entry->getPath()];
    }

    $matches = \glob($entry->getPath());

    if ($matches === false) {
      return [];
    }

    return $matches;
  }

  private function applyOwnership(OwnershipEntry $entry, string $path): void {
    $owner = $entry->getOwner();
    $group = $entry->getGroup();

    if ($owner === null && $group === null) {
      return;
    }

    foreach ($this->ownershipTargets($entry, $path) as $target) {
      $this->chown($target, $owner, $group);
    }
  }

  /**
   * @return list<string>
   */
  private function ownershipTargets(OwnershipEntry $entry, string $path): array {
    if (!$entry->isRecursive()) {
      return [$path];
    }

    return $this->collectPaths($path);
  }

  /**
   * @return list<string>
   */
  private function collectPaths(string $root): array {
    $paths = [$root];

    if (!\is_dir($root)) {
      return $paths;
    }

    $iterator = new RecursiveIteratorIterator(
      new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
      RecursiveIteratorIterator::SELF_FIRST,
    );

    foreach ($iterator as $item) {
      $paths[] = $item->getPathname();
    }

    return $paths;
  }

  private function chown(string $path, ?string $owner, ?string $group): void {
    if ($owner !== null && !@\lchown($path, $owner)) {
      throw OwnershipException::ownerFailed($path, $owner);
    }

    if ($group !== null && !@\lchgrp($path, $group)) {
      throw OwnershipException::groupFailed($path, $group);
    }
  }

  private function applyMode(OwnershipEntry $entry, string $path): void {
    $mode = $entry->getMode();

    if ($mode === null) {
      return;
    }

    if (\is_link($path)) {
      throw OwnershipException::symlinkRefused($path);
    }

    if (!@\chmod($path, $mode)) {
      throw OwnershipException::modeFailed($path, $mode);
    }
  }
}
