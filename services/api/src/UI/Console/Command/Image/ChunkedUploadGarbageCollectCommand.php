<?php

declare(strict_types=1);

namespace UI\Console\Command\Image;

use Slink\Image\Infrastructure\ChunkedUpload\Storage\ChunkStorageInterface;
use Slink\Image\Infrastructure\ChunkedUpload\UploadTokenCodec;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
  name: 'image:chunked-upload:gc',
  description: 'Removes orphaned chunked-upload fragments older than the token TTL plus a grace period'
)]
final class ChunkedUploadGarbageCollectCommand extends Command {
  private const int DEFAULT_GRACE = 7200;

  public function __construct(
    private readonly ChunkStorageInterface $chunkStorage,
  ) {
    parent::__construct();
  }

  protected function configure(): void {
    $this->addOption('grace', null, InputOption::VALUE_REQUIRED, 'Additional grace period in seconds', (string) self::DEFAULT_GRACE);
  }

  protected function execute(InputInterface $input, OutputInterface $output): int {
    $io = new SymfonyStyle($input, $output);

    $grace = (int) $input->getOption('grace');
    $cutoff = \time() - UploadTokenCodec::TTL - $grace;

    $deleted = 0;

    foreach ($this->chunkStorage->listUploadIds() as $uploadId) {
      $lastModified = $this->chunkStorage->lastModified($uploadId);

      if ($lastModified === null || $lastModified >= $cutoff) {
        continue;
      }

      $this->chunkStorage->deleteUpload($uploadId);
      $deleted++;
    }

    $io->success(\sprintf('Removed %d orphaned chunked upload(s).', $deleted));

    return Command::SUCCESS;
  }
}
