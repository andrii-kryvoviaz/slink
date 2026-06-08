<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\ChunkedUpload;

use Slink\Image\Application\Command\UploadImage\UploadImageCommand;
use Slink\Image\Infrastructure\ChunkedUpload\Storage\ChunkStorageInterface;
use Slink\Shared\Application\Command\CommandBusInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final readonly class ChunkedUploadCompleter {
  public function __construct(
    private ChunkStorageInterface $chunkStorage,
    private CommandBusInterface $commandBus,
    private ValidatorInterface $validator,
    private LockFactory $lockFactory,
  ) {
  }

  public function complete(UploadToken $token): CompletionResult {
    $lock = $this->lockFactory->createLock('chunked-upload-' . $token->getUploadId());
    $lock->acquire(true);

    try {
      return $this->completeExclusively($token);
    } finally {
      $lock->release();
    }
  }

  private function completeExclusively(UploadToken $token): CompletionResult {
    $existing = $this->chunkStorage->findCompletedImageId($token->getUploadId());

    if ($existing !== null) {
      return new CompletionResult($existing, false);
    }

    $this->assertChunksComplete($token);

    $imageId = $this->assembleAndDispatch($token);
    $this->chunkStorage->markComplete($token->getUploadId(), $imageId);

    return new CompletionResult($imageId, true);
  }

  private function assertChunksComplete(UploadToken $token): void {
    $missing = $this->missingChunks($token->getUploadId(), $token->getTotalChunks());

    if ($missing === []) {
      return;
    }

    throw new HttpException(
      Response::HTTP_UNPROCESSABLE_ENTITY,
      \sprintf('Missing chunks: %s.', \implode(', ', $missing)),
    );
  }

  private function assembleAndDispatch(UploadToken $token): string {
    $file = $this->chunkStorage->assemble(
      $token->getUploadId(),
      \range(0, $token->getTotalChunks() - 1),
      $token->getFileName(),
    );

    try {
      $this->assertAssembledSize($file, $token);
      $command = $this->buildCommand($file, $token);
      $this->validateCommand($command);
      $this->commandBus->handleSync($command->withContext(['userId' => $token->getOwnerId()]));

      return $command->getId()->toString();
    } finally {
      $this->deleteIfExists($file);
    }
  }

  private function deleteIfExists(File $file): void {
    if (\file_exists($file->getPathname())) {
      @\unlink($file->getPathname());
    }
  }

  /**
   * @return array<int>
   */
  private function missingChunks(string $uploadId, int $totalChunks): array {
    $received = $this->chunkStorage->listChunkIndexes($uploadId);
    $missing = [];

    for ($index = 0; $index < $totalChunks; $index++) {
      if (!\in_array($index, $received, true)) {
        $missing[] = $index;
      }
    }

    return $missing;
  }

  private function assertAssembledSize(File $file, UploadToken $token): void {
    if ($file->getSize() !== $token->getTotalSize()) {
      throw new HttpException(
        Response::HTTP_UNPROCESSABLE_ENTITY,
        'The assembled file size does not match the declared total size.',
      );
    }
  }

  private function buildCommand(File $file, UploadToken $token): UploadImageCommand {
    return new UploadImageCommand(
      $file,
      $token->isPublic(),
      $token->getDescription(),
      $token->getTagIds(),
      $token->getCollectionIds(),
    );
  }

  private function validateCommand(UploadImageCommand $command): void {
    $violations = $this->validator->validate($command);

    if (\count($violations) > 0) {
      throw new HttpException(
        Response::HTTP_UNPROCESSABLE_ENTITY,
        \implode("\n", \array_map(static fn($violation) => $violation->getMessage(), \iterator_to_array($violations))),
        new ValidationFailedException($command, $violations),
      );
    }
  }
}
