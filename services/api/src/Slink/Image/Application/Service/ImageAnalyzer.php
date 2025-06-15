<?php

declare(strict_types=1);

namespace Slink\Image\Application\Service;

use Slink\Image\Domain\Service\ImageAnalyzerInterface;
use Slink\Image\Grpc\ImageServiceClient;
use Slink\Image\Grpc\ImageInfoRequest;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\File;

final class ImageAnalyzer implements ImageAnalyzerInterface {
  /**
   * @param array<string> $resizableMimeTypes
   * @param array<string> $stripExifMimeTypes
   * @param array<string> $enforceConversionMimeTypes
   */
  public function __construct(
    #[Autowire(param: 'supports_resize')]
    private readonly array $resizableMimeTypes,
    #[Autowire(param: 'supports_strip_exif')]
    private readonly array $stripExifMimeTypes,
    #[Autowire(param: 'enforce_conversion')]
    private readonly array $enforceConversionMimeTypes,
    private readonly ImageServiceClient $grpcImageServiceClient
  ) {
  }
  
  private File $file;
  
  /**
   * @var int|null
   */
  private ?int $width = null;
  
  /**
   * @var int|null
   */
  private ?int $height = null;
  
  public function setFile(File $file): self {
    $this->file = $file;
    
    return $this;
  }
  
  /**
   * @param File $file
   * @return array<string, mixed>
   */
  public function analyze(File $file): array {
    $this->setFile($file);

    $content = file_get_contents($file->getPathname());
    if ($content !== false) {
      $request = new ImageInfoRequest();
      $request->setImageData($content);

      $response = $this->grpcImageServiceClient->GetImageInfo($request)->wait();
      list($reply, $status) = $response;

      if ($status->code === \Grpc\STATUS_OK && $reply->hasInfo()) {
        $imageInfo = $reply->getInfo();
        $this->width = $imageInfo->getWidth();
        $this->height = $imageInfo->getHeight();
        return $this->toPayload();
      }
    }

    [$this->width, $this->height] = getimagesize($file->getPathname()) ?: [1, 1];
    
    return $this->toPayload();
  }
  
  /**
   * @param ?string $mimeType
   * @return bool
   */
  public function supportsResize(?string $mimeType): bool {
    return \in_array($mimeType, $this->resizableMimeTypes, true);
  }
  
  /**
   * @param ?string $mimeType
   * @return bool
   */
  public function supportsExifProfile(?string $mimeType): bool {
    return \in_array($mimeType, $this->stripExifMimeTypes, true);
  }
  
  /**
   * @param ?string $mimeType
   * @return bool
   */
  public function isConversionRequired(?string $mimeType): bool {
    return \in_array($mimeType, $this->enforceConversionMimeTypes, true);
  }
  
  /**
   * @return ?string
   */
  public function getMimeType(): ?string {
    return $this->file->getMimeType();
  }
  
  /**
   * @return int
   */
  public function getSize(): int {
    return $this->file->getSize();
  }
  
  /**
   * @return int
   */
  public function getTimeCreated(): int {
    return $this->file->getCTime();
  }
  
  /**
   * @return int
   */
  public function getTimeModified(): int {
    return $this->file->getMTime();
  }
  
  /**
   * @return int
   */
  public function getWidth(): int {
    return $this->width ?? 0;
  }
  
  /**
   * @return int
   */
  public function getHeight(): int {
    return $this->height ?? 0;
  }
  
  /**
   * @return float
   */
  public function getAspectRatio(): float {
    if(!$this->width || !$this->height) {
      return 0;
    }
    
    return $this->width / $this->height;
  }
  
  /**
   * @return string
   */
  public function getOrientation(): string {
    if ($this->width === null || $this->height === null) {
      return 'unknown';
    }
    
    if ($this->width === $this->height) {
      return 'square';
    }
    
    return $this->width > $this->height ? 'landscape' : 'portrait';
  }
  
  /**
   * @return array<string, mixed>
   */
  public function toPayload(): array {
    return [
      'mimeType' => $this->getMimeType(),
      'size' => $this->getSize(),
      'timeCreated' => $this->getTimeCreated(),
      'timeModified' => $this->getTimeModified(),
      'width' => $this->getWidth(),
      'height' => $this->getHeight(),
      'aspectRatio' => $this->getAspectRatio(),
      'orientation' => $this->getOrientation(),
    ];
  }
}