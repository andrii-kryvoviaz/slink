<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Upload;

use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Tests\Integration\Http\HttpTestCase;

final class UploadValidationTest extends HttpTestCase {
  private string $token = '';

  private function bootUser(): void {
    $this->createUser('uploader@local.test', 'uploaderuser', self::PASSWORD);
    $this->token = $this->login('uploaderuser', self::PASSWORD);
  }

  private function upload(UploadedFile $file): int {
    $this->client->request(
      'POST',
      '/api/upload',
      [],
      ['image' => $file],
      ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->token],
    );

    return $this->client->getResponse()->getStatusCode();
  }

  private function textFile(): UploadedFile {
    $temp = (string) \tempnam(\sys_get_temp_dir(), 'slink_upload_') . '.txt';
    \file_put_contents($temp, 'this is definitely not an image payload');

    return new UploadedFile($temp, 'note.txt', 'text/plain', null, true);
  }

  private function corruptImage(): UploadedFile {
    $temp = (string) \tempnam(\sys_get_temp_dir(), 'slink_upload_') . '.png';
    \file_put_contents($temp, "\x89PNG\r\n\x1a\n" . \str_repeat("\x00", 64));

    return new UploadedFile($temp, 'broken.png', 'image/png', null, true);
  }

  private function oversizedPng(int $padding): UploadedFile {
    $temp = (string) \tempnam(\sys_get_temp_dir(), 'slink_upload_') . '.png';

    $width = 2;
    $height = 2;

    $ihdr = \pack('N', $width)
      . \pack('N', $height)
      . \chr(8)
      . \chr(2)
      . \chr(0)
      . \chr(0)
      . \chr(0);

    $raw = '';
    for ($y = 0; $y < $height; $y++) {
      $raw .= \chr(0);
      for ($x = 0; $x < $width * 3; $x++) {
        $raw .= \chr(0);
      }
    }

    $compressed = \gzcompress($raw, 9);
    if ($compressed === false) {
      throw new \RuntimeException('Unable to compress test image data.');
    }

    $text = "Comment\x00" . \str_repeat('A', $padding);

    $png = "\x89PNG\r\n\x1a\n"
      . $this->pngChunk('IHDR', $ihdr)
      . $this->pngChunk('IDAT', $compressed)
      . $this->pngChunk('tEXt', $text)
      . $this->pngChunk('IEND', '');

    \file_put_contents($temp, $png);

    return new UploadedFile($temp, 'large.png', 'image/png', null, true);
  }

  private function pngChunk(string $type, string $data): string {
    return \pack('N', \strlen($data))
      . $type
      . $data
      . \pack('N', \crc32($type . $data));
  }

  #[Test]
  public function nonImageFileIsRejected(): void {
    $this->bootUser();

    self::assertSame(422, $this->upload($this->textFile()));
  }

  #[Test]
  public function corruptImageIsRejected(): void {
    $this->bootUser();

    self::assertSame(422, $this->upload($this->corruptImage()));
  }

  #[Test]
  public function oversizedImageIsRejected(): void {
    $this->bootUser();

    $file = $this->oversizedPng(6 * 1024 * 1024);
    self::assertGreaterThan(5 * 1024 * 1024, (int) $file->getSize());

    self::assertSame(422, $this->upload($file));
  }
}
