<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\Image\ChunkedUpload;

use Slink\Image\Infrastructure\ChunkedUpload\Storage\ChunkStorageInterface;
use Slink\Image\Infrastructure\ChunkedUpload\UploadToken;
use Slink\Shared\Infrastructure\Security\Voter\GuestAccessVoter;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[Route(path: '/upload/chunked/{uploadId}', name: 'chunked_upload_status', methods: ['GET'])]
#[IsGranted(GuestAccessVoter::GUEST_UPLOAD_ALLOWED)]
final readonly class StatusController {
  public function __construct(
    private ChunkStorageInterface $chunkStorage,
  ) {
  }

  public function __invoke(
    string $uploadId,
    UploadToken $token,
  ): ApiResponse {
    $completedImageId = $this->chunkStorage->findCompletedImageId($uploadId);

    if ($completedImageId !== null) {
      return ApiResponse::fromPayload([
        'data' => [
          'uploadId' => $token->getUploadId(),
          'fileName' => $token->getFileName(),
          'totalSize' => $token->getTotalSize(),
          'receivedChunks' => $this->fullRange($token->getTotalChunks()),
          'complete' => true,
          'id' => $completedImageId,
        ],
      ]);
    }

    $receivedChunks = $this->chunkStorage->listChunkIndexes($uploadId);

    return ApiResponse::fromPayload([
      'data' => [
        'uploadId' => $token->getUploadId(),
        'fileName' => $token->getFileName(),
        'totalSize' => $token->getTotalSize(),
        'receivedChunks' => $receivedChunks,
        'complete' => false,
      ],
    ]);
  }

  /**
   * @return array<int>
   */
  private function fullRange(int $totalChunks): array {
    return $totalChunks > 0 ? \range(0, $totalChunks - 1) : [];
  }
}
