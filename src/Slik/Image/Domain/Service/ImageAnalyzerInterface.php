<?php

namespace Slik\Image\Domain\Service;

use Symfony\Component\HttpFoundation\File\File;

interface ImageAnalyzerInterface {
  /**
   * @param File $file
   * @return self
   */
  public static function fromFile(File $file): self;
  
  /**
   * @param string $path
   * @return self
   */
  public static function fromPath(string $path): self;
  
  /**
   * @param File $file
   * @return void
   */
  public function analyze(File $file): void;
 
  /**
   * @return array
   */
  public function toPayload(): array;
}