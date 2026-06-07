<?php

declare(strict_types=1);

namespace Tests\Support;

use Jcupitt\Vips\Image as VipsImage;
use Symfony\Component\HttpFoundation\File\UploadedFile;

trait ImageFormatFixtures {
  /**
   * @var array<string, array{ext: string, mime: string}>
   */
  private const array FORMAT_MAP = [
    'jpeg' => ['ext' => 'jpg', 'mime' => 'image/jpeg'],
    'png' => ['ext' => 'png', 'mime' => 'image/png'],
    'gif' => ['ext' => 'gif', 'mime' => 'image/gif'],
    'webp' => ['ext' => 'webp', 'mime' => 'image/webp'],
    'avif' => ['ext' => 'avif', 'mime' => 'image/avif'],
    'bmp' => ['ext' => 'bmp', 'mime' => 'image/bmp'],
    'tiff' => ['ext' => 'tiff', 'mime' => 'image/tiff'],
    'heic' => ['ext' => 'heic', 'mime' => 'image/heic'],
    'svg' => ['ext' => 'svg', 'mime' => 'image/svg+xml'],
    'ico' => ['ext' => 'ico', 'mime' => 'image/x-icon'],
    'tga' => ['ext' => 'tga', 'mime' => 'image/x-tga'],
  ];

  private const array RESIZABLE_FORMATS = ['jpeg', 'png', 'gif', 'webp', 'avif', 'bmp'];

  private const array ANIMATED_FORMATS = ['gif', 'webp'];

  private const array CONVERT_FORMATS = ['ico', 'tga'];

  private const string SVG_TEMPLATE =
    '<svg xmlns="http://www.w3.org/2000/svg" width="%1$d" height="%2$d">'
    . '<rect width="%1$d" height="%2$d" fill="#123456"/></svg>';

  /**
   * @var array<int, string>
   */
  private array $_registeredTempFiles = [];

  protected function imageBytes(string $format, int $width = 32, int $height = 32): string {
    self::assertKnownFormat($format);

    if ($format === 'svg') {
      return \sprintf(self::SVG_TEMPLATE, $width, $height);
    }

    if (\in_array($format, self::CONVERT_FORMATS, true)) {
      return $this->convertBytes($format, $width, $height);
    }

    return self::canvas($width, $height)->writeToBuffer('.' . self::formatExtension($format));
  }

  protected function animatedImageBytes(string $format, int $frames = 3, int $width = 32, int $height = 32): string {
    if (!\in_array($format, self::ANIMATED_FORMATS, true)) {
      throw new \InvalidArgumentException(\sprintf('Animated bytes are only supported for: %s.', \implode(', ', self::ANIMATED_FORMATS)));
    }

    $pages = [];
    for ($i = 0; $i < $frames; $i++) {
      $pages[] = VipsImage::black($width, $height, ['bands' => 3])->add(40 * ($i + 1))->cast('uchar');
    }

    $joined = VipsImage::arrayjoin($pages, ['across' => 1])->copy();
    $joined->set('page-height', $height);
    $joined->set('n-pages', $frames);
    $joined->set('delay', \array_fill(0, $frames, 100));

    return $joined->writeToBuffer('.' . self::formatExtension($format));
  }

  protected function uploadedImage(string $format, ?string $bytes = null): UploadedFile {
    self::assertKnownFormat($format);

    $payload = $bytes ?? $this->imageBytes($format);
    $extension = self::formatExtension($format);
    $temp = \tempnam(\sys_get_temp_dir(), 'slink_format_fixture_') . '.' . $extension;

    \file_put_contents($temp, $payload);
    $this->registerTempFile($temp);

    return new UploadedFile($temp, 'fixture.' . $extension, self::formatMimeType($format), null, true);
  }

  public static function formatExtension(string $format): string {
    self::assertKnownFormat($format);

    return self::FORMAT_MAP[$format]['ext'];
  }

  public static function formatMimeType(string $format): string {
    self::assertKnownFormat($format);

    return self::FORMAT_MAP[$format]['mime'];
  }

  /**
   * @return array<int, string>
   */
  public static function allFormatKeys(): array {
    return \array_keys(self::FORMAT_MAP);
  }

  /**
   * @return array<int, string>
   */
  public static function resizableFormatKeys(): array {
    return self::RESIZABLE_FORMATS;
  }

  /**
   * @return array<int, string>
   */
  public static function animatedFormatKeys(): array {
    return self::ANIMATED_FORMATS;
  }

  private static function canvas(int $width, int $height): VipsImage {
    return VipsImage::black($width, $height, ['bands' => 3])->add(120)->cast('uchar');
  }

  private function convertBytes(string $format, int $width, int $height): string {
    if (!self::convertAvailable()) {
      throw new \RuntimeException('ImageMagick "convert" is required to generate ' . $format . ' fixtures.');
    }

    $source = \tempnam(\sys_get_temp_dir(), 'slink_format_src_') . '.png';
    $target = \tempnam(\sys_get_temp_dir(), 'slink_format_dst_') . '.' . self::formatExtension($format);

    self::canvas($width, $height)->writeToFile($source);

    [$exitCode, $stderr] = self::runProcess(['convert', $source, $target]);

    if ($exitCode !== 0 || !\is_file($target)) {
      @\unlink($source);
      @\unlink($target);

      throw new \RuntimeException(\sprintf('Failed to generate %s fixture via convert: %s', $format, $stderr));
    }

    $bytes = (string) \file_get_contents($target);

    @\unlink($source);
    @\unlink($target);

    return $bytes;
  }

  private static function convertAvailable(): bool {
    [$exitCode] = self::runProcess(['convert', '-version']);

    return $exitCode === 0;
  }

  /**
   * @param list<string> $command
   * @return array{0: int, 1: string}
   */
  private static function runProcess(array $command): array {
    $descriptors = [1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
    $process = \proc_open($command, $descriptors, $pipes);

    if (!\is_resource($process)) {
      return [-1, 'Unable to start process.'];
    }

    \stream_get_contents($pipes[1]);
    $stderr = (string) \stream_get_contents($pipes[2]);

    foreach ($pipes as $pipe) {
      \fclose($pipe);
    }

    return [\proc_close($process), $stderr];
  }

  private function registerTempFile(string $path): void {
    $this->_registeredTempFiles[] = $path;

    \register_shutdown_function(static function () use ($path): void {
      if (\is_file($path)) {
        @\unlink($path);
      }
    });
  }

  private static function assertKnownFormat(string $format): void {
    if (!isset(self::FORMAT_MAP[$format])) {
      throw new \InvalidArgumentException(\sprintf('Unknown image format key "%s".', $format));
    }
  }
}
