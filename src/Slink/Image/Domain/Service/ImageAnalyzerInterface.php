<?php

namespace Slink\Image\Domain\Service;

use Symfony\Component\HttpFoundation\File\File;

interface ImageAnalyzerInterface {
  /**
   * @param File $file
   * @return void
   */
  public function analyze(File $file): void;
  
  /**
   * @param string $mimeType
   * @return bool
   */
  public function supportsResize(string $mimeType): bool;
 
  /**
   * @return array<string, mixed>
   */
  public function toPayload(): array;
}