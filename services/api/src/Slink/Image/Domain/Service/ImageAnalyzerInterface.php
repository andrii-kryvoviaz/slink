<?php

namespace Slink\Image\Domain\Service;

use Symfony\Component\HttpFoundation\File\File;

interface ImageAnalyzerInterface {
  /**
   * @param File $file
   * @return array<string, mixed>
   */
  public function analyze(File $file): array;
  
  /**
   * @param ?string $mimeType
   * @return bool
   */
  public function supportsResize(?string $mimeType): bool;
  
  /**
   * @param ?string $mimeType
   * @return bool
   */
  public function supportsExifProfile(?string $mimeType): bool;
  
  /**
   * @param ?string $mimeType
   * @return bool
   */
  public function isConversionRequired(?string $mimeType): bool;
  
  /**
   * @param ?string $mimeType
   * @return bool
   */
  public function requiresSanitization(?string $mimeType): bool;
 
  /**
   * @return array<string, mixed>
   */
  public function toPayload(): array;
}