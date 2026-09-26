<?php

declare(strict_types=1);

namespace UI\Console\Command\Demo;

use Slink\Image\Application\Command\UploadImage\UploadImageCommand;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Shared\Application\Command\CommandTrait;
use Slink\Shared\Domain\ValueObject\ID;
use Symfony\Component\HttpFoundation\File\File;

final class DemoImageSeeder {
  use CommandTrait;

  private const array SUPPORTED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif', 'bmp', 'tga', 'svg'];

  public function __construct(
    private readonly ImageRepositoryInterface $imageRepository,
  ) {
  }

  /**
   * @return array<string, ImageView>
   */
  public function seed(ID $userId, string $picturesPath, DemoSeedReport $report): array {
    $images = [];

    foreach ($this->pictureFileNames($picturesPath) as $fileName) {
      $image = $report->attempt($fileName, fn() => $this->ensureImage($userId, $picturesPath . '/' . $fileName, $report));

      if ($image !== null) {
        $images[$fileName] = $image;
      }
    }

    return $images;
  }

  /**
   * @return list<string>
   */
  private function pictureFileNames(string $picturesPath): array {
    $fileNames = array_filter(
      scandir($picturesPath) ?: [],
      fn(string $fileName): bool => $this->isPicture($picturesPath . '/' . $fileName),
    );
    sort($fileNames);

    return $fileNames;
  }

  private function isPicture(string $path): bool {
    $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

    return is_file($path) && in_array($extension, self::SUPPORTED_EXTENSIONS, true);
  }

  private function ensureImage(ID $userId, string $path, DemoSeedReport $report): ImageView {
    $fileName = basename($path);
    $hash = sha1_file($path) ?: throw new \RuntimeException('Unable to hash the picture');
    $existing = $this->imageRepository->findBySha1Hash($hash, $userId);

    if ($existing !== null) {
      $report->skipped($fileName);

      return $existing;
    }

    $command = new UploadImageCommand(new File($path), isPublic: true);
    $this->handle($command->withContext(['userId' => $userId->toString()]));
    $report->created($fileName);

    return $this->imageRepository->oneById($command->getId()->toString());
  }
}
