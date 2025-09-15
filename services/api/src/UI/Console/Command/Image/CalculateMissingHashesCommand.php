<?php

declare(strict_types=1);

namespace UI\Console\Command\Image;

use Slink\Image\Application\Command\CalculateImageHash\CalculateImageHashCommand;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Shared\Application\Command\CommandBusInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
  name: 'image:calculate-missing-hashes',
  description: 'Calculate SHA-1 hashes for existing images without hashes'
)]
final class CalculateMissingHashesCommand extends Command {
  public function __construct(
    private readonly ImageRepositoryInterface $imageRepository,
    private readonly CommandBusInterface      $commandBus
  ) {
    parent::__construct();
  }

  protected function configure(): void {
    $this->addOption(
      'batch-size',
      'b',
      InputOption::VALUE_OPTIONAL,
      'Number of images to process in each batch',
      100
    );

    $this->addOption(
      'dry-run',
      null,
      InputOption::VALUE_NONE,
      'Show what would be processed without making changes'
    );
  }

  protected function execute(InputInterface $input, OutputInterface $output): int {
    $io = new SymfonyStyle($input, $output);
    $batchSize = (int)$input->getOption('batch-size');
    $isDryRun = $input->getOption('dry-run');

    if ($isDryRun) {
      $io->note('Running in DRY RUN mode - no changes will be made');
    }

    $io->title('Calculating SHA-1 hashes for images');

    $imagesWithoutHash = $this->imageRepository->findImagesWithoutSha1Hash();
    $totalImages = count($imagesWithoutHash);

    if ($totalImages === 0) {
      $io->success('All images already have SHA-1 hashes calculated');
      return Command::SUCCESS;
    }

    $io->note(sprintf('Found %d images without SHA-1 hashes', $totalImages));

    if (!$input->getOption('no-interaction') && !$io->confirm('Do you want to continue?', true)) {
      return Command::SUCCESS;
    }

    $progressBar = $io->createProgressBar($totalImages);
    $progressBar->start();

    $processed = 0;
    $errors = 0;

    foreach ($imagesWithoutHash as $imageView) {
      try {
        if (!$isDryRun) {
          $imageId = ID::fromString($imageView->getUuid());
          $command = new CalculateImageHashCommand($imageId);
          $this->commandBus->handle($command);
        }

        $processed++;

      } catch (\Exception $e) {
        $io->error(sprintf(
          'Error processing image %s: %s',
          $imageView->getFileName(),
          $e->getMessage()
        ));
        $errors++;
      }

      $progressBar->advance();

      if ($processed % $batchSize === 0) {
        $io->writeln(sprintf(' Processed %d images...', $processed));
      }
    }

    $progressBar->finish();
    $io->newLine(2);

    if ($isDryRun) {
      $io->info(sprintf('DRY RUN: Would process %d images', $processed));
    } else {
      $io->success(sprintf(
        'Successfully processed %d images with %d errors',
        $processed,
        $errors
      ));
    }

    return $errors > 0 ? Command::FAILURE : Command::SUCCESS;
  }
}