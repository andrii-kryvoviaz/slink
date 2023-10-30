<?php

declare(strict_types=1);

namespace Slik\Shared\Infrastructure\FileSystem\Storage;

use Symfony\Component\HttpFoundation\File\File;

interface StorageInterface {
  public function upload(File $file, string $fileName, ?string $path = null): void;
}