<?php

declare(strict_types=1);

namespace UI\Console\Command\Demo;

use Slink\Image\Application\Command\UploadImage\UploadImageCommand;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Application\Command\CommandTrait;
use Slink\User\Domain\Repository\UserRepositoryInterface;
use Slink\User\Domain\ValueObject\Username;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\File\File;

#[AsCommand(
  name: 'slink:demo:seed',
  description: 'Seeds demo instance with images from mounted pictures directory'
)]
final class SeedDemoCommand extends Command {
  use CommandTrait;

  private const DEMO_PICTURES_PATH = '/app/docker/demo/pictures';
  private const SUPPORTED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif', 'bmp', 'tga', 'svg'];

  public function __construct(
    private readonly ConfigurationProviderInterface $configurationProvider,
    private readonly UserRepositoryInterface        $userRepository
  ) {
    parent::__construct();
  }

  protected function execute(InputInterface $input, OutputInterface $output): int {
    $io = new SymfonyStyle($input, $output);

    if (!$this->configurationProvider->get('demo.enabled')) {
      $io->error('Demo mode is not enabled');
      return Command::FAILURE;
    }

    if (!is_dir(self::DEMO_PICTURES_PATH)) {
      $io->error('Demo pictures directory not found: ' . self::DEMO_PICTURES_PATH);
      return Command::FAILURE;
    }

    if ($this->hasExistingImages()) {
      $io->warning('Demo instance already has images uploaded. Skipping seed operation.');
      return Command::SUCCESS;
    }

    try {
      $demoUsername = $this->configurationProvider->get('demo.demoUsername');
      $demoUser = $this->userRepository->oneByUsername(Username::fromString($demoUsername));
      
      $io->info('Scanning demo pictures directory for images...');
      $imageFiles = $this->scanImageFiles();
      
      if (empty($imageFiles)) {
        $io->warning('No image files found in demo pictures directory');
        return Command::SUCCESS;
      }

      $io->info(sprintf('Found %d image files to upload', count($imageFiles)));
      $io->progressStart(count($imageFiles));

      $uploadedCount = 0;
      foreach ($imageFiles as $imageFile) {
        try {
          $file = new File($imageFile);
          $command = new UploadImageCommand(
            image: $file,
            isPublic: true,
          );

          $this->handle($command->withContext([
            'userId' => $demoUser->getUuid()
          ]));
          $uploadedCount++;
          $io->progressAdvance();
        } catch (\Exception $e) {
          $io->writeln(sprintf('<error>Failed to upload %s: %s</error>', basename($imageFile), $e->getMessage()));
        }
      }

      $io->progressFinish();
      $io->success(sprintf('Successfully uploaded %d out of %d images to demo instance', $uploadedCount, count($imageFiles)));

      return Command::SUCCESS;
    } catch (\Exception $e) {
      $io->error('Failed to seed demo images: ' . $e->getMessage());
      return Command::FAILURE;
    }
  }

  private function hasExistingImages(): bool {
    $imagesDir = $this->configurationProvider->get('storage.adapter.local.dir') . '/slink/images';
    
    if (!is_dir($imagesDir)) {
      return false;
    }

    $files = scandir($imagesDir);
    if ($files === false) {
      return false;
    }

    $imageFiles = array_filter($files, function ($file) use ($imagesDir) {
      if (in_array($file, ['.', '..', '.DS_Store'])) {
        return false;
      }
      return is_file($imagesDir . '/' . $file);
    });

    return !empty($imageFiles);
  }

  /**
   * @return array<string>
   */
  private function scanImageFiles(): array {
    $files = scandir(self::DEMO_PICTURES_PATH);
    if ($files === false) {
      return [];
    }

    $imageFiles = [];
    foreach ($files as $file) {
      if (in_array($file, ['.', '..', '.DS_Store'])) {
        continue;
      }

      $filePath = self::DEMO_PICTURES_PATH . '/' . $file;
      if (!is_file($filePath)) {
        continue;
      }

      $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
      if (in_array($extension, self::SUPPORTED_EXTENSIONS)) {
        $imageFiles[] = $filePath;
      }
    }

    return $imageFiles;
  }
}
