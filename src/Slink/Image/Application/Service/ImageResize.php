<?php

declare(strict_types=1);

namespace Slink\Image\Application\Service;

use Exception;
use Imagick;
use RuntimeException;
use Slink\Image\Domain\Enum\ImageCropPosition;

// ToDo: Consider to use a standalone service to reduce CPU usage
final class ImageResize
{
  private bool $gammaCorrect = false;
  private int $interlace = 1;
  protected Imagick $sourceImage;
  protected int $originalWidth {
    get {
      return $this->originalWidth;
    }
  }
  protected int $originalHeight {
    get {
      return $this->originalHeight;
    }
  }
  protected int $sourceX;
  protected int $sourceY;
  protected int $destWidth {
    get {
      return $this->destWidth;
    }
  }
  protected int $destHeight {
    get {
      return $this->destHeight;
    }
  }
  protected int $sourceWidth;
  protected int $sourceHeight;
  
  /**
   * @param string $imageContent
   * @return ImageResize
   * @throws RuntimeException
   * @throws \ImagickException
   */
  public static function createFromString(string $imageContent): ImageResize {
    return new ImageResize($imageContent);
  }
  
  /**
   * @param string $imageContent
   * @throws RuntimeException|\ImagickException
   */
  public function __construct(string $imageContent)
  {
    $this->sourceImage = new Imagick();
    
    try {
      $this->sourceImage->readImageBlob($imageContent);
    } catch (Exception $e) {
      throw new RuntimeException('Could not read image from input data');
    }
    
    $this->sourceImage->autoOrient();
    
    $this->originalWidth = $this->sourceImage->getImageWidth();
    $this->originalHeight = $this->sourceImage->getImageHeight();
  }
  
  /**
   * @param bool $enable
   * @return static
   */
  public function withGammaCorrection(bool $enable = false): ImageResize {
    $this->gammaCorrect = $enable;
    
    return $this;
  }
  
  /**
   * @param string $filename
   * @param int|null $quality
   * @param integer|null $permissions
   * @param boolean|array<int> $exactSize
   * @return static
   * @throws \ImagickException
   */
  public function save(
    string     $filename,
    ?int       $quality = null,
    ?int       $permissions = null,
    bool|array $exactSize = false
  ): ImageResize {
    $imagick = $this->sourceImage;
    
    $isAnimated = ($imagick->getNumberImages() > 1);
    if ($isAnimated) {
      $imagick = $imagick->coalesceImages();
    }
    
    if (!empty($exactSize) && is_array($exactSize)) {
      $destWidth = $exactSize[0];
      $destHeight = $exactSize[1];
    } else {
      $destWidth = $this->destWidth;
      $destHeight = $this->destHeight;
    }
    
    if ($this->gammaCorrect) {
      $imagick->gammaImage(1/2.2);
    }
    
    if (
      ($this->sourceWidth < $imagick->getImageWidth()) ||
      ($this->sourceHeight < $imagick->getImageHeight()) ||
      ($this->sourceX > 0) ||
      ($this->sourceY > 0)
    ) {
      foreach ($imagick as $frame) {
        $frame->cropImage($this->sourceWidth, $this->sourceHeight, $this->sourceX, $this->sourceY);
        $frame->setImagePage($this->sourceWidth, $this->sourceHeight, 0, 0);
      }
    }
    
    if ($isAnimated) {
      foreach ($imagick as $frame) {
        $frame->resizeImage($destWidth, $destHeight, Imagick::FILTER_LANCZOS, 1);
        $frame->setImagePage($destWidth, $destHeight, 0, 0);
      }
      $imagick = $imagick->deconstructImages();
    } else {
      $imagick->resizeImage($destWidth, $destHeight, Imagick::FILTER_LANCZOS, 1);
    }
    
    if ($this->gammaCorrect) {
      $imagick->gammaImage(2.2);
    }
    
    $imagick->setInterlaceScheme($this->interlace ? Imagick::INTERLACE_LINE : Imagick::INTERLACE_NO);
    
    if ($quality !== null) {
      $imagick->setImageCompressionQuality($quality);
    }
    
    if ($isAnimated) {
      $imagick->writeImages($filename, true);
    } else {
      $imagick->writeImage($filename);
    }
    if ($permissions) {
      chmod($filename, $permissions);
    }
    
    $imagick->clear();
    
    return $this;
  }
  
  /**
   * @param int|null $quality
   * @return ?string
   * @throws \ImagickException
   */
  public function getImageAsString(?int $quality = null): ?string {
    $stringTemp = tempnam(sys_get_temp_dir(), '');
    $this->save($stringTemp, $quality);
    
    $string = file_get_contents($stringTemp);
    unlink($stringTemp);
    
    if ($string === false) {
      return null;
    }
    
    return $string;
  }
  
  /**
   * @param integer $maxShort
   * @param boolean $allowEnlarge
   * @return static
   */
  public function resizeToShortSide(int $maxShort, bool $allowEnlarge = false): ImageResize {
    if ($this->originalHeight < $this->originalWidth) {
      $ratio = $maxShort / $this->originalHeight;
      $long = (int) round($this->originalWidth * $ratio);
      $this->resize($long, $maxShort, $allowEnlarge);
    } else {
      $ratio = $maxShort / $this->originalWidth;
      $long = (int) round($this->originalHeight * $ratio);
      $this->resize($maxShort, $long, $allowEnlarge);
    }
    return $this;
  }
  
  /**
   * @param integer $maxLong
   * @param boolean $allowEnlarge
   * @return static
   */
  public function resizeToLongSide(int $maxLong, bool $allowEnlarge = false): ImageResize {
    if ($this->originalHeight > $this->originalWidth) {
      $ratio = $maxLong / $this->originalHeight;
      $short = (int) round($this->originalWidth * $ratio);
      $this->resize($short, $maxLong, $allowEnlarge);
    } else {
      $ratio = $maxLong / $this->originalWidth;
      $short = (int) round($this->originalHeight * $ratio);
      $this->resize($maxLong, $short, $allowEnlarge);
    }
    return $this;
  }
  
  /**
   * @param integer $height
   * @param boolean $allowEnlarge
   * @return static
   */
  public function resizeToHeight(int $height, bool $allowEnlarge = false): ImageResize {
    $ratio = $height / $this->originalHeight;
    $width = (int) round($this->originalWidth * $ratio);
    $this->resize($width, $height, $allowEnlarge);
    return $this;
  }
  
  /**
   * @param integer $width
   * @param boolean $allowEnlarge
   * @return static
   */
  public function resizeToWidth(int $width, bool $allowEnlarge = false): ImageResize {
    $ratio  = $width / $this->originalWidth;
    $height = (int) round($this->originalHeight * $ratio);
    $this->resize($width, $height, $allowEnlarge);
    return $this;
  }
  
  /**
   * @param integer $maxWidth
   * @param integer $maxHeight
   * @param boolean $allowEnlarge
   * @return static
   */
  public function resizeToBestFit(int $maxWidth, int $maxHeight, bool $allowEnlarge = false): ImageResize {
    if ($this->originalWidth <= $maxWidth &&
      $this->originalHeight <= $maxHeight &&
      $allowEnlarge === false) {
      return $this;
    }
    
    $ratio  = $this->originalHeight / $this->originalWidth;
    $width  = $maxWidth;
    $height = (int) round($width * $ratio);
    if ($height > $maxHeight) {
      $height = $maxHeight;
      $width  = (int) round($height / $ratio);
    }
    
    return $this->resize($width, $height, $allowEnlarge);
  }
  
  /**
   * @param float|integer $scale
   * @return static
   */
  public function scale(float|int $scale): ImageResize {
    $width  = (int) round($this->originalWidth * $scale / 100);
    $height = (int) round($this->originalHeight * $scale / 100);
    $this->resize($width, $height, true);
    return $this;
  }
  
  /**
   * @param integer $width
   * @param integer $height
   * @param boolean $allowEnlarge
   * @return static
   */
  public function resize(int $width, int $height, bool $allowEnlarge = false): ImageResize {
    if (!$allowEnlarge) {
      // If not allowed to enlarge, clamp to original size if bigger.
      if ($width > $this->originalWidth || $height > $this->originalHeight) {
        $width  = $this->originalWidth;
        $height = $this->originalHeight;
      }
    }
    
    $this->sourceX = 0;
    $this->sourceY = 0;
    
    $this->destWidth = $width;
    $this->destHeight = $height;
    
    $this->sourceWidth = $this->originalWidth;
    $this->sourceHeight = $this->originalHeight;
    
    return $this;
  }
  
  /**
   * @param integer $width
   * @param integer $height
   * @param boolean $allowEnlarge
   * @param ImageCropPosition $position
   * @return static
   */
  public function crop(int $width, int $height, bool $allowEnlarge = false, ImageCropPosition $position = ImageCropPosition::CROP_CENTER): ImageResize {
    if (!$allowEnlarge) {
      if ($width > $this->originalWidth) {
        $width  = $this->originalWidth;
      }
      if ($height > $this->originalHeight) {
        $height = $this->originalHeight;
      }
    }
    
    $ratioSource = $this->originalWidth / $this->originalHeight;
    $ratioDest   = $width / $height;
    
    if ($ratioDest < $ratioSource) {
      $this->resizeToHeight($height, $allowEnlarge);
      $excess_width = (int) round(
        ($this->destWidth - $width) * $this->originalWidth / $this->destWidth
      );
      $this->sourceWidth = $this->originalWidth - $excess_width;
      $this->sourceX = $this->getCropPosition($excess_width, $position);
      $this->destWidth   = $width;
    } else {
      $this->resizeToWidth($width, $allowEnlarge);
      $excessHeight = (int) round(
        ($this->destHeight - $height) * $this->originalHeight / $this->destHeight
      );
      $this->sourceHeight = $this->originalHeight - $excessHeight;
      $this->sourceY = $this->getCropPosition($excessHeight, $position);
      $this->destHeight   = $height;
    }
    
    return $this;
  }
  
  /**
   * @param integer $width
   * @param integer $height
   * @param false|integer $x
   * @param false|integer $y
   * @return static
   */
  public function freeCrop(int $width, int $height, false|int $x = false, false|int $y = false): static {
    if ($x === false || $y === false) {
      return $this->crop($width, $height);
    }
    
    $this->sourceX = $x;
    $this->sourceY = $y;
    
    if ($width > $this->originalWidth - $x) {
      $this->sourceWidth = $this->originalWidth - $x;
    } else {
      $this->sourceWidth = $width;
    }
    
    if ($height > $this->originalHeight - $y) {
      $this->sourceHeight = $this->originalHeight - $y;
    } else {
      $this->sourceHeight = $height;
    }
    
    $this->destWidth = $width;
    $this->destHeight = $height;
    
    return $this;
  }
  
  /**
   * @param integer $expectedSize
   * @param ImageCropPosition $position
   * @return integer
   */
  protected function getCropPosition(int $expectedSize, ImageCropPosition $position = ImageCropPosition::CROP_CENTER): int {
    $size = match ($position) {
      ImageCropPosition::CROP_BOTTOM, ImageCropPosition::CROP_RIGHT => $expectedSize,
      ImageCropPosition::CROP_CENTER => $expectedSize / 2,
      ImageCropPosition::CROP_TOP_CENTER => $expectedSize / 4,
      default => 0,
    };
    
    return (int) round($size);
  }
}